import {
  getEprotectCustomErrorMessages,
  getEprotectLabels,
  getEprotectPlaceholderText,
  getMissingEprotectFields,
  loadEprotectScript,
  mapWorldpayCardType,
  resolveEprotectErrorMessage,
  resolveEprotectStyle,
} from "../adapters/eprotectAdapter.js";
import { shouldMountEprotect } from "../state/checkoutSelectors.js";

const PAYFRAME_READY_DEFAULT_TIMEOUT_MS = 6000;
const PAYFRAME_READY_TIMEOUT_BUFFER_MS = 1000;
const PAYFRAME_READY_MAX_TIMEOUT_MS = 8000;
const EXISTING_IFRAME_SETTLE_MS = 250;
const INSERTED_IFRAME_FALLBACK_MS = 2000;
const EPROTECT_DEFAULT_TIMEOUT_MS = 30000;

function resolveEprotectTimeoutMs(config) {
  const configuredTimeoutMs = Number(config?.timeoutMs);

  return Number.isFinite(configuredTimeoutMs) && configuredTimeoutMs > 0 ? configuredTimeoutMs : EPROTECT_DEFAULT_TIMEOUT_MS;
}

function resolvePayframeReadyTimeoutMs(config) {
  const configuredTimeoutMs = resolveEprotectTimeoutMs(config);

  return Math.min(configuredTimeoutMs + PAYFRAME_READY_TIMEOUT_BUFFER_MS, PAYFRAME_READY_MAX_TIMEOUT_MS);
}

export function createEprotectService({ store, payframeElement }) {
  let pendingMount = null;
  let pendingTokenization = null;
  let mountAttemptId = 0;
  let activePayframeReadyWaiter = null;

  function setPaymentStatusMessage(message = "", isError = false) {
    store.dispatch({
      type: "PAYMENT_STATUS_CHANGED",
      message,
      isError,
    });
  }

  function setVisible(visible) {
    store.dispatch({
      type: "EPROTECT_VISIBILITY_CHANGED",
      visible,
    });
  }

  function setLoading(loading) {
    store.dispatch({
      type: "EPROTECT_LOADING_CHANGED",
      loading,
    });
  }

  function isCurrentMountAttempt(id) {
    return id === mountAttemptId;
  }

  function createPayframeReadyWaiter(timeoutMs = PAYFRAME_READY_DEFAULT_TIMEOUT_MS) {
    let resolved = false;
    let observer = null;
    let deadlineId = null;
    let iframeFallbackId = null;
    let observedIframe = null;
    let resolvePromise;
    const startedAt = Date.now();

    function clearObserver() {
      if (observer) {
        observer.disconnect();
        observer = null;
      }
    }

    function cleanup() {
      clearObserver();
      window.clearTimeout(deadlineId);
      window.clearTimeout(iframeFallbackId);
      if (observedIframe) {
        observedIframe.removeEventListener("load", handleIframeLoad);
      }
    }

    function hasIframe() {
      return Boolean(observedIframe || payframeElement?.querySelector("iframe"));
    }

    function finish(ready = hasIframe()) {
      if (resolved) {
        return;
      }

      resolved = true;
      cleanup();
      resolvePromise(Boolean(ready));
    }

    function handleIframeLoad() {
      finish(true);
    }

    function getRemainingTime() {
      return Math.max(0, timeoutMs - (Date.now() - startedAt));
    }

    function watchIframe(iframe, alreadyPresent = false) {
      if (!iframe || observedIframe === iframe) {
        return false;
      }

      observedIframe = iframe;
      observedIframe.addEventListener("load", handleIframeLoad, { once: true });
      clearObserver();

      const settleDelay = alreadyPresent ? EXISTING_IFRAME_SETTLE_MS : Math.min(INSERTED_IFRAME_FALLBACK_MS, Math.max(EXISTING_IFRAME_SETTLE_MS, getRemainingTime()));
      iframeFallbackId = window.setTimeout(() => finish(true), settleDelay);
      return true;
    }

    const promise = new Promise((resolve) => {
      resolvePromise = resolve;

      if (!payframeElement) {
        finish(false);
        return;
      }

      if (watchIframe(payframeElement.querySelector("iframe"), true)) {
        deadlineId = window.setTimeout(() => finish(), timeoutMs);
        return;
      }

      if (typeof window.MutationObserver === "function") {
        observer = new MutationObserver(() => {
          watchIframe(payframeElement.querySelector("iframe"));
        });
        observer.observe(payframeElement, { childList: true, subtree: true });
      }

      deadlineId = window.setTimeout(() => finish(), timeoutMs);
    });

    return {
      promise,
      cancel() {
        finish(false);
      },
    };
  }

  function clearPayframe() {
    if (payframeElement) {
      payframeElement.innerHTML = "";
    }
  }

  function clearActivePayframeReadyWaiter() {
    if (activePayframeReadyWaiter) {
      activePayframeReadyWaiter.cancel();
      activePayframeReadyWaiter = null;
    }
  }

  function teardown() {
    mountAttemptId += 1;
    pendingMount = null;

    clearActivePayframeReadyWaiter();

    if (pendingTokenization) {
      const { resolve, timeoutId } = pendingTokenization;
      window.clearTimeout(timeoutId);
      pendingTokenization = null;
      resolve({
        ok: false,
        error: "El pago seguro se reinició. Volvé a intentar.",
      });
    }

    clearPayframe();
    store.dispatch({
      type: "EPROTECT_CLIENT_SET",
      client: null,
      ready: false,
    });
    setLoading(false);
    setVisible(false);
    setPaymentStatusMessage("");
  }

  async function mount() {
    if (pendingMount) {
      return pendingMount;
    }

    const mountPromise = mountEprotect().finally(() => {
      if (pendingMount === mountPromise) {
        pendingMount = null;
      }
    });
    pendingMount = mountPromise;

    return mountPromise;
  }

  async function mountEprotect() {
    const currentMountAttemptId = ++mountAttemptId;
    const state = store.getState();

    if (!shouldMountEprotect(state)) {
      teardown();
      return false;
    }

    const missingFields = getMissingEprotectFields();
    if (missingFields.length > 0) {
      setVisible(true);
      clearActivePayframeReadyWaiter();
      setLoading(false);
      store.dispatch({ type: "EPROTECT_READY_CHANGED", ready: false });
      setPaymentStatusMessage(`Checkout no disponible: faltan ${missingFields.join(", ")} en la configuración de pagos.`, true);
      return false;
    }

    setVisible(true);

    if (state.eprotectClient) {
      setLoading(false);
      store.dispatch({ type: "EPROTECT_READY_CHANGED", ready: true });
      setPaymentStatusMessage("Completá los datos de tu tarjeta.");
      return true;
    }

    setLoading(true);
    store.dispatch({ type: "EPROTECT_READY_CHANGED", ready: false });
    setPaymentStatusMessage("");

    try {
      await loadEprotectScript();

      if (!isCurrentMountAttempt(currentMountAttemptId)) {
        return false;
      }

      const latestState = store.getState();
      if (!shouldMountEprotect(latestState)) {
        teardown();
        return false;
      }

      if (latestState.eprotectClient) {
        setLoading(false);
        store.dispatch({ type: "EPROTECT_READY_CHANGED", ready: true });
        setPaymentStatusMessage("Completá los datos de tu tarjeta.");
        return true;
      }

      clearPayframe();
      const config = window.APP?.PAYMENTS?.EPROTECT;
      const eprotectTimeoutMs = resolveEprotectTimeoutMs(config);
      const payframeReadyWaiter = createPayframeReadyWaiter(resolvePayframeReadyTimeoutMs(config));
      activePayframeReadyWaiter = payframeReadyWaiter;

      const payframeHeight = window.matchMedia("(max-width: 768px)").matches ? "350" : "250";
      const client = new window.EprotectIframeClient({
        paypageId: config.paypageId,
        reportGroup: config.reportGroup,
        style: resolveEprotectStyle(config),
        timeout: String(eprotectTimeoutMs),
        div: "eprotect-payframe",
        height: payframeHeight,
        showCvv: true,
        months: { 1: "01", 2: "02", 3: "03", 4: "04", 5: "05", 6: "06", 7: "07", 8: "08", 9: "09", 10: "10", 11: "11", 12: "12" },
        numYears: 15,
        expYearFormat: "YY",
        tabIndex: {
          accountNumber: 1,
          expMonth: 2,
          expYear: 3,
          cvv: 4,
        },
        placeholderText: getEprotectPlaceholderText(),
        htmlTimeout: String(eprotectTimeoutMs),
        clearCvvMaskOnReturn: true,
        label: getEprotectLabels(),
        customErrorMessages: getEprotectCustomErrorMessages(),
        enhancedUxFeatures: {
          inlineFieldValidations: true,
          numericInputsOnly: true,
          enhancedUxVersion: 2,
          coloredCardNetworkLogos: true,
          cVVValidation: true,
          expDateValidation: false,
        },
        callback: handleResponse,
      });

      store.dispatch({
        type: "EPROTECT_CLIENT_SET",
        client,
        ready: false,
      });

      const payframeReady = await payframeReadyWaiter.promise;

      if (activePayframeReadyWaiter === payframeReadyWaiter) {
        activePayframeReadyWaiter = null;
      }

      if (!isCurrentMountAttempt(currentMountAttemptId)) {
        return false;
      }

      if (!payframeReady) {
        throw new Error("No se pudo cargar el formulario seguro de pago.");
      }

      if (!shouldMountEprotect(store.getState())) {
        teardown();
        return false;
      }

      setLoading(false);
      store.dispatch({ type: "EPROTECT_READY_CHANGED", ready: true });
      setPaymentStatusMessage("Completá los datos de tu tarjeta.");
      return true;
    } catch (error) {
      if (!isCurrentMountAttempt(currentMountAttemptId)) {
        return false;
      }

      clearActivePayframeReadyWaiter();
      clearPayframe();
      setLoading(false);
      store.dispatch({
        type: "EPROTECT_CLIENT_SET",
        client: null,
        ready: false,
      });
      setPaymentStatusMessage(error.message || "No se pudo cargar eProtect.", true);
      return false;
    }
  }

  function resolvePendingTokenization(result) {
    if (!pendingTokenization) {
      return;
    }

    const { resolve, timeoutId } = pendingTokenization;
    window.clearTimeout(timeoutId);
    pendingTokenization = null;
    resolve(result);
  }

  function handleResponse(response) {
    if (!pendingTokenization) {
      return;
    }

    if (response.response !== "870") {
      resolvePendingTokenization({
        ok: false,
        error: resolveEprotectErrorMessage(response),
      });
      return;
    }

    const ccType = mapWorldpayCardType(response.type);
    if (ccType === null) {
      resolvePendingTokenization({
        ok: false,
        error: "No se pudo reconocer el tipo de tarjeta.",
      });
      return;
    }

    resolvePendingTokenization({
      ok: true,
      payment: {
        worldPayLowValueToken: response.paypageRegistrationId,
        firstSixDigitsCCNumber: response.firstSix || null,
        lastFourDigitsCCNumber: response.lastFour || null,
        ccExpMonth: response.expMonth || null,
        ccExpYear: response.expYear || null,
        ccType,
      },
    });
  }

  function tokenize() {
    const state = store.getState();

    if (!state.eprotectClient || !state.eprotectReady) {
      return Promise.resolve({
        ok: false,
        error: "El iframe de pago seguro no está listo todavía.",
      });
    }

    if (pendingTokenization) {
      return pendingTokenization.promise;
    }

    const timestamp = Date.now().toString();
    let resolveTokenization;
    const promise = new Promise((resolve) => {
      resolveTokenization = resolve;
    });

    const timeoutId = window.setTimeout(() => {
      pendingTokenization = null;
      resolveTokenization({
        ok: false,
        error: "No se pudo validar la tarjeta. Intentá nuevamente.",
      });
    }, resolveEprotectTimeoutMs(window.APP?.PAYMENTS?.EPROTECT));

    pendingTokenization = {
      resolve: resolveTokenization,
      timeoutId,
      promise,
    };

    try {
      state.eprotectClient.getPaypageRegistrationId({
        id: timestamp,
        orderId: `order_${timestamp}`,
      });
    } catch (error) {
      window.clearTimeout(timeoutId);
      pendingTokenization = null;
      resolveTokenization({
        ok: false,
        error: error.message || "No se pudo validar la tarjeta. Intentá nuevamente.",
      });
    }

    return promise;
  }

  return {
    mount,
    teardown,
    tokenize,
  };
}
