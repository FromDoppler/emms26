import { updateCheckoutEvents } from "../../checkoutStorage.js";
import { createCheckoutSuccessComponents } from "./components/checkoutSuccessComponents.js";
import { renderSuccessReceipt } from "./components/successReceiptComponent.js";
import { getPayment } from "./checkoutSuccessService.js";
import { trackApprovedVipPurchaseOnce } from "./checkoutSuccessTracking.js";

const PAYMENT_POLL_DELAY_MS = 1500;
const PAYMENT_POLL_ATTEMPTS = 20;
const KNOWN_PAYMENT_STATUSES = new Set(["pending", "processing", "approved", "rejected", "error"]);
const PROCESSING_PAYMENT_STATUSES = new Set(["pending", "processing"]);

class ProcessingPaymentError extends Error {
  constructor(message = "Tu pago sigue en proceso. Actualizá esta página en unos segundos o revisá tu correo.") {
    super(message);
    this.name = "ProcessingPaymentError";
    this.title = "Tu pago sigue en proceso";
  }
}

function wait(milliseconds) {
  return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
}

function isPlainObject(value) {
  return Boolean(value) && typeof value === "object" && !Array.isArray(value);
}

function normalizeContractText(value) {
  return typeof value === "string" ? value.trim().toLowerCase() : null;
}

function normalizePaymentStatus(payload = {}) {
  return normalizeContractText(payload.payment?.status) || "";
}

function normalizeProjectionPaymentStatus(payload = {}) {
  return normalizeContractText(payload.payment?.status) || "";
}

function paymentIdFrom(payload = {}) {
  const paymentId = payload.payment?.paymentId;

  return typeof paymentId === "string" && paymentId.trim() !== "" ? paymentId.trim() : null;
}

function isValidPaymentProjection(payload, expectedPaymentId) {
  if (!isPlainObject(payload) || !isPlainObject(payload.payment)) {
    return false;
  }

  const paymentStatus = normalizeProjectionPaymentStatus(payload);
  const envelopeStatus = normalizeContractText(payload.status);

  return (
    paymentIdFrom(payload) === expectedPaymentId &&
    KNOWN_PAYMENT_STATUSES.has(paymentStatus) &&
    envelopeStatus !== null &&
    envelopeStatus === paymentStatus &&
    typeof payload.success === "boolean" &&
    payload.success === (paymentStatus === "approved")
  );
}

function resolvePaymentErrorMessage(paymentStatus, payload = {}) {
  if (paymentStatus === "rejected") {
    return "El pago fue rechazado. Revisá el medio de pago o intentá nuevamente.";
  }

  if (paymentStatus === "error") {
    return "No pudimos confirmar la compra. Si el pago fue debitado, contactá a soporte.";
  }

  const error = String(payload.error || "")
    .trim()
    .toLowerCase();
  if (error === "invalid_json_response" || error === "server_error") {
    return "No pudimos consultar el pago. Intentá nuevamente en unos minutos.";
  }

  return payload.message || "No pudimos validar la compra.";
}

function classifySuccessGetResult(result, expectedPaymentId) {
  if (!result || result.parseError) {
    return { kind: "ambiguous" };
  }

  const payload = result.payload;
  if (!isPlainObject(payload)) {
    return { kind: "ambiguous" };
  }

  const hasPayment = Object.prototype.hasOwnProperty.call(payload, "payment");
  const error = normalizeContractText(payload.error);

  if (result.status === 422 && !hasPayment && payload.success === false && error === "validation_error") {
    return { kind: "invalid_reference", result };
  }

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

  const paymentStatus = normalizePaymentStatus(payload);
  if (PROCESSING_PAYMENT_STATUSES.has(paymentStatus)) {
    return { kind: "processing", result };
  }

  if (paymentStatus === "approved") {
    return { kind: "approved", payment: payload.payment };
  }

  if (paymentStatus === "rejected" || paymentStatus === "error") {
    return { kind: "terminal_error", result };
  }

  return { kind: "ambiguous" };
}

async function getApprovedPayment(paymentId) {
  let lastClassification = { kind: "ambiguous" };

  for (let attempt = 0; attempt < PAYMENT_POLL_ATTEMPTS; attempt += 1) {
    let result = null;

    try {
      result = await getPayment(paymentId);
    } catch (error) {
      result = null;
    }

    const classification = classifySuccessGetResult(result, paymentId);
    lastClassification = classification;

    if (classification.kind === "approved") {
      return classification.payment;
    }

    if (classification.kind === "processing") {
      if (attempt < PAYMENT_POLL_ATTEMPTS - 1) {
        await wait(PAYMENT_POLL_DELAY_MS);
        continue;
      }

      throw new ProcessingPaymentError();
    }

    if (classification.kind === "not_found") {
      throw new Error("No encontramos ese pago. Revisá el payment_id o intentá nuevamente.");
    }

    if (classification.kind === "invalid_reference") {
      throw new Error("La referencia de pago no es válida.");
    }

    if (classification.kind === "identity_mismatch") {
      throw new Error("No pudimos validar la identidad del pago.");
    }

    if (classification.kind === "terminal_error") {
      const paymentStatus = normalizePaymentStatus(result.payload);
      throw new Error(resolvePaymentErrorMessage(paymentStatus, result.payload));
    }

    if (attempt < PAYMENT_POLL_ATTEMPTS - 1) {
      await wait(PAYMENT_POLL_DELAY_MS);
      continue;
    }

    throw new Error("No pudimos consultar el pago.");
  }

  if (lastClassification.kind === "processing") {
    throw new ProcessingPaymentError();
  }

  if (lastClassification.kind === "not_found") {
    throw new Error("No encontramos ese pago. Revisá el payment_id o intentá nuevamente.");
  }

  throw new Error("No se pudo consultar el pago.");
}

export async function initCheckoutSuccess() {
  const successView = createCheckoutSuccessComponents();
  successView.setSpinnerVisible(true);

  const urlParams = new URLSearchParams(window.location.search);
  const paymentId = String(urlParams.get("payment_id") || "").trim();

  if (!paymentId) {
    successView.showErrorState("Falta payment_id en la URL.");
    successView.setSpinnerVisible(false);
    return;
  }

  try {
    const payment = await getApprovedPayment(paymentId);

    renderSuccessReceipt(payment);
    updateCheckoutEvents();
    successView.setContainerVisible(true);
    trackApprovedVipPurchaseOnce(paymentId, payment);
  } catch (error) {
    successView.showErrorState(error.message || "No se pudo consultar el pago.", error.title);
  } finally {
    successView.setSpinnerVisible(false);
  }
}
