import { persistDplridFromCustomerEmail } from "../../../checkoutStorage.js";
import { createPayment, getPayment } from "./checkoutService.js";
import { setStatusMessage, setSpinnerVisible } from "../components/checkoutComponents.js";
import { shouldMountEprotect } from "../state/checkoutSelectors.js";
import { buildCreatePaymentPayload, getCustomerPayload } from "../checkoutPayloads.js";
import { resolvePaymentErrorMessage } from "../checkoutMessages.js";
import { createPaymentId } from "../state/checkoutState.js";

const PAYMENT_POLL_ATTEMPTS = 3;
const PAYMENT_POLL_DELAY_MS = 900;
const KNOWN_PAYMENT_STATUSES = new Set(["pending", "processing", "approved", "rejected", "error"]);
const TERMINAL_PAYMENT_STATUSES = new Set(["approved", "rejected", "error"]);
const PRE_LEDGER_422_ERRORS = new Set([
  "validation_error",
  "customer_email_required",
  "customer_name_required",
  "customer_phone_required",
  "privacy_required",
  "ticket_unavailable",
  "coupon_invalid",
  "coupon_inactive",
  "coupon_expired",
  "coupon_out_of_scope",
  "coupon_discount_type_unsupported",
]);

function wait(milliseconds) {
  return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
}

function isPlainObject(value) {
  return Boolean(value) && typeof value === "object" && !Array.isArray(value);
}

function normalizeContractText(value) {
  return typeof value === "string"
    ? value.trim().toLowerCase()
    : null;
}

function paymentStatus(result) {
  return normalizeContractText(result?.payload?.payment?.status || result?.payload?.status) || "";
}

function paymentIdFrom(payload = {}) {
  const paymentId = payload.payment?.paymentId;

  return typeof paymentId === "string" && paymentId.trim() !== "" ? paymentId.trim() : null;
}

function isValidPaymentProjection(payload, expectedPaymentId) {
  if (!isPlainObject(payload) || !isPlainObject(payload.payment)) {
    return false;
  }

  const paymentId = paymentIdFrom(payload);
  const paymentStatusValue = normalizeContractText(payload.payment.status);
  const envelopeStatus = normalizeContractText(payload.status);

  return (
    paymentId !== null &&
    paymentId === expectedPaymentId &&
    paymentStatusValue !== null &&
    envelopeStatus !== null &&
    KNOWN_PAYMENT_STATUSES.has(paymentStatusValue) &&
    envelopeStatus === paymentStatusValue &&
    typeof payload.success === "boolean" &&
    payload.success === (paymentStatusValue === "approved")
  );
}

function classifyCreateResult(result, expectedPaymentId) {
  if (!result || result.parseError) {
    return { kind: "ambiguous" };
  }

  const payload = result.payload;
  if (!isPlainObject(payload)) {
    return { kind: "ambiguous" };
  }

  const hasPayment = Object.prototype.hasOwnProperty.call(payload, "payment");
  const payment = isPlainObject(payload.payment) ? payload.payment : null;
  const error = normalizeContractText(payload.error);

  if (result.status === 409 && !hasPayment && payload.success === false && error === "payment_intent_conflict") {
    return { kind: "intent_conflict", result };
  }

  if (result.status === 422 && !hasPayment && payload.success === false && PRE_LEDGER_422_ERRORS.has(error || "")) {
    return { kind: "pre_ledger_rejection", result };
  }

  if (!isValidPaymentProjection(payload, expectedPaymentId)) {
    return { kind: "ambiguous" };
  }

  const status = paymentStatus(result);
  if (result.status === 202 && status === "processing") {
    return { kind: "processing", result };
  }

  if (result.status === 200 && TERMINAL_PAYMENT_STATUSES.has(status)) {
    return { kind: "terminal", result };
  }

  return { kind: "ambiguous" };
}

function classifyRecoveryGetResult(result, expectedPaymentId) {
  if (!result || result.parseError) {
    return { kind: "ambiguous" };
  }

  const payload = result.payload;
  if (!isPlainObject(payload)) {
    return { kind: "ambiguous" };
  }

  const hasPayment = Object.prototype.hasOwnProperty.call(payload, "payment");
  const error = normalizeContractText(payload.error);

  if (result.status === 404 && !hasPayment && payload.success === false && error === "payment_not_found") {
    return { kind: "not_found", result };
  }

  if (result.status !== 200 || !isPlainObject(payload.payment)) {
    return { kind: "ambiguous" };
  }

  const paymentId = paymentIdFrom(payload);
  if (!paymentId) {
    return { kind: "ambiguous" };
  }

  if (paymentId !== expectedPaymentId) {
    return { kind: "identity_mismatch", result };
  }

  if (!isValidPaymentProjection(payload, expectedPaymentId)) {
    return { kind: "ambiguous" };
  }

  const status = paymentStatus(result);
  if (status === "pending" || status === "processing") {
    return { kind: "processing", result };
  }

  if (TERMINAL_PAYMENT_STATUSES.has(status)) {
    return { kind: "terminal", result };
  }

  return { kind: "ambiguous" };
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

export function createSubmitService({ store, view, eprotect, paymentApi = { createPayment, getPayment } }) {
  let activeAttempt = null;
  let activePromise = null;

  async function recover(attempt) {
    let result = null;
    let classification = { kind: "ambiguous" };

    try {
      result = await paymentApi.createPayment(attempt.serializedBody);
    } catch (error) {
      result = null;
    }

    classification = classifyCreateResult(result, attempt.paymentId);
    if (classification.kind === "terminal" || classification.kind === "pre_ledger_rejection" || classification.kind === "intent_conflict") {
      return {
        result,
        classification,
      };
    }

    for (let index = 0; index < PAYMENT_POLL_ATTEMPTS; index += 1) {
      await wait(PAYMENT_POLL_DELAY_MS);

      try {
        result = await paymentApi.getPayment(attempt.paymentId);
      } catch (error) {
        result = null;
      }

      classification = classifyRecoveryGetResult(result, attempt.paymentId);
      if (classification.kind === "terminal" || classification.kind === "identity_mismatch") {
        break;
      }
    }

    return {
      result,
      classification,
    };
  }

  function finishApproved(attempt) {
    const successUrl = buildSuccessUrl(store.getState().successPath, attempt.paymentId);

    if (!attempt.approvedFinished) {
      attempt.approvedFinished = true;

      try {
        persistDplridFromCustomerEmail(attempt.customerEmail);
      } catch (error) {
        // Browser identity persistence is best effort after durable approval.
      }
    }

    window.location.replace(successUrl);
    return true;
  }

  function showPaymentIdentityError(attempt) {
    setStatusMessage(view.checkoutStatus, `No pudimos validar la identidad del pago. Contactá a soporte con la referencia ${attempt.paymentId}.`, true);
  }

  function showPaymentVerificationError(attempt) {
    setStatusMessage(view.checkoutStatus, `No pudimos verificar el estado del pago. Contactá a soporte con la referencia ${attempt.paymentId}.`, true);
  }

  async function runAttempt(attempt) {
    const recovery = await recover(attempt);
    const { result, classification } = recovery;
    if (!result) {
      showPaymentVerificationError(attempt);
      return false;
    }

    const status = paymentStatus(result);

    if (classification.kind === "intent_conflict") {
      setStatusMessage(view.checkoutStatus, `No pudimos validar la intención del pago. Contactá a soporte con la referencia ${attempt.paymentId}.`, true);
      return false;
    }

    if (classification.kind === "pre_ledger_rejection") {
      setActiveAttempt(null);
      setStatusMessage(view.checkoutStatus, "No pudimos validar los datos ingresados. Revisalos e intentá nuevamente.", true);
      return false;
    }

    if (classification.kind === "identity_mismatch") {
      showPaymentIdentityError(attempt);
      return false;
    }

    if (classification.kind === "processing") {
      setStatusMessage(view.checkoutStatus, `Tu pago sigue en proceso. Referencia: ${attempt.paymentId}`);
      return false;
    }

    if (classification.kind === "ambiguous") {
      showPaymentVerificationError(attempt);
      return false;
    }

    if (classification.kind !== "terminal") {
      showPaymentVerificationError(attempt);
      return false;
    }

    if (status === "approved") {
      return finishApproved(attempt);
    }

    if (status === "rejected" || status === "error") {
      setActiveAttempt(null);
      setStatusMessage(view.checkoutStatus, resolvePaymentErrorMessage(result.payload.error), true);
      return false;
    }

    showPaymentVerificationError(attempt);
    return false;
  }

  async function executeSubmit({ customerPayload, snapshot } = {}) {
    setSpinnerVisible(view, true);
    store.dispatch({ type: "PAYMENT_IN_FLIGHT_CHANGED", paymentInFlight: true, customerPayload });
    setStatusMessage(view.checkoutStatus, "");

    try {
      if (activeAttempt) {
        return activeAttempt.approvedFinished ? finishApproved(activeAttempt) : await runAttempt(activeAttempt);
      }

      let paymentPayload = {};

      if (shouldMountEprotect(store.getState())) {
        const tokenization = await eprotect.tokenize();
        if (!tokenization.ok) {
          setStatusMessage(view.checkoutStatus, tokenization.error || "No se pudo validar la tarjeta.", true);
          return false;
        }

        paymentPayload = tokenization.payment || {};
      }

      const paymentId = createPaymentId();
      if (!paymentId) {
        setStatusMessage(view.checkoutStatus, "Tu navegador no puede crear un identificador de pago seguro.", true);
        return false;
      }

      const state = store.getState();
      const currentSnapshot = snapshot || {};
      const customer = customerPayload || getCustomerPayload(state, currentSnapshot);
      const body = buildCreatePaymentPayload(state, currentSnapshot, paymentPayload, customer, paymentId);

      setActiveAttempt({
        paymentId,
        serializedBody: JSON.stringify(body),
        customerEmail: customer.email,
        approvedFinished: false,
      });

      return await runAttempt(activeAttempt);
    } catch (error) {
      const reference = activeAttempt?.paymentId;
      setStatusMessage(view.checkoutStatus, reference ? `No pudimos verificar el pago. Volvé a intentarlo con la misma referencia ${reference}.` : "No se pudo completar el pago.", true);
      return false;
    } finally {
      store.dispatch({ type: "PAYMENT_IN_FLIGHT_CHANGED", paymentInFlight: false, customerPayload: null });
      setSpinnerVisible(view, false);
    }
  }

  function submit(options = {}) {
    if (activePromise) {
      return activePromise;
    }

    const operation = Promise.resolve().then(() => executeSubmit(options));
    activePromise = operation.finally(() => {
      activePromise = null;
    });
    return activePromise;
  }

  function setActiveAttempt(attempt) {
    activeAttempt = attempt;
    store.dispatch({
      type: "PAYMENT_ATTEMPT_CHANGED",
      paymentId: attempt?.paymentId || null,
    });
  }

  return {
    submit,
    hasActiveAttempt: () => activeAttempt !== null,
  };
}
