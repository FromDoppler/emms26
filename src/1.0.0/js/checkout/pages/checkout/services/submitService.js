import { persistDplridFromCustomerEmail } from "../../../checkoutStorage.js";
import { createPayment } from "./checkoutService.js";
import { setStatusMessage, setSpinnerVisible } from "../components/checkoutComponents.js";
import { shouldMountEprotect } from "../state/checkoutSelectors.js";
import { buildCreatePaymentPayload, getCustomerPayload } from "../checkoutPayloads.js";
import { rotateIdempotencyKey } from "../state/checkoutState.js";

const REDIRECTABLE_PAYMENT_STATUSES = new Set(["approved", "pending", "processing"]);

function getPaymentPublicId(payload = {}) {
  return String(payload.payment?.publicId || payload.payment?.paymentId || payload.publicId || payload.paymentId || payload.payment_id || "").trim();
}

function getPaymentStatus(result) {
  return String(result.payload?.payment?.status || result.payload?.status || (result.status === 202 ? "processing" : ""))
    .trim()
    .toLowerCase();
}

function buildSuccessUrl(successPath, paymentId) {
  const url = new URL(successPath || "/checkout-success", window.location.origin);
  const currentParams = new URLSearchParams(window.location.search);

  ["utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content", "origin"].forEach((param) => {
    const value = currentParams.get(param);
    if (value) {
      url.searchParams.set(param, value);
    }
  });

  url.searchParams.set("payment_id", paymentId);
  return `${url.pathname}${url.search}${url.hash}`;
}

function redirectToSuccess({ store, customerPayload, snapshot, paymentId }) {
  persistDplridFromCustomerEmail(customerPayload?.email || getCustomerPayload(store.getState(), snapshot).email);
  window.location.href = buildSuccessUrl(store.getState().successPath, paymentId);
}

export function createSubmitService({ store, view, eprotect }) {
  async function submit({ customerPayload, snapshot } = {}) {
    const state = store.getState();
    if (state.paymentInFlight) {
      return false;
    }

    if (state.lastPaymentOutcome === "rejected" || state.lastPaymentOutcome === "error") {
      rotateIdempotencyKey(store);
      store.dispatch({ type: "PAYMENT_OUTCOME_CHANGED", outcome: null });
    }

    setSpinnerVisible(view, true);
    store.dispatch({
      type: "PAYMENT_IN_FLIGHT_CHANGED",
      paymentInFlight: true,
      customerPayload,
    });
    setStatusMessage(view.checkoutStatus, "");

    try {
      let paymentPayload = {};

      if (shouldMountEprotect(store.getState())) {
        const tokenization = await eprotect.tokenize();
        if (!tokenization.ok) {
          store.dispatch({
            type: "PAYMENT_STATUS_CHANGED",
            message: tokenization.error || "No se pudo validar la tarjeta.",
            isError: true,
          });
          return false;
        }

        paymentPayload = tokenization.payment || {};
      }

      const currentState = store.getState();
      const currentSnapshot = snapshot || {};
      const result = await createPayment(buildCreatePaymentPayload(currentState, currentSnapshot, paymentPayload, customerPayload));
      const paymentStatus = getPaymentStatus(result);
      const paymentPublicId = getPaymentPublicId(result.payload);

      if (paymentStatus === "rejected" || paymentStatus === "error" || (result.status >= 400 && result.status !== 202)) {
        store.dispatch({
          type: "PAYMENT_OUTCOME_CHANGED",
          outcome: paymentStatus === "rejected" ? "rejected" : "error",
        });
      }

      if (paymentPublicId && REDIRECTABLE_PAYMENT_STATUSES.has(paymentStatus)) {
        redirectToSuccess({ store, customerPayload, snapshot: currentSnapshot, paymentId: paymentPublicId });
        return true;
      }

      if (result.status === 202) {
        setStatusMessage(view.checkoutStatus, "Tu pago está siendo procesado. Esperá unos segundos e intentá nuevamente.");
        return false;
      }

      setStatusMessage(view.checkoutStatus, result.payload.error || result.payload.status || "No se pudo completar el pago.", result.status >= 400 || result.payload.success === false);
      return false;
    } catch (error) {
      setStatusMessage(view.checkoutStatus, "No se pudo completar el pago.", true);
      return false;
    } finally {
      store.dispatch({
        type: "PAYMENT_IN_FLIGHT_CHANGED",
        paymentInFlight: false,
        customerPayload: null,
      });
      setSpinnerVisible(view, false);
    }
  }

  return { submit };
}
