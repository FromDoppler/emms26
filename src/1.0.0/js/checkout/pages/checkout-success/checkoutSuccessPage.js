import { persistDplridFromCustomerEmail, updateCheckoutEvents } from "../../checkoutStorage.js";
import { createCheckoutSuccessComponents } from "./components/checkoutSuccessComponents.js";
import { getPaymentCustomerEmail, renderSuccessReceipt } from "./components/successReceiptComponent.js";
import { getPayment } from "./checkoutSuccessService.js";
import { trackApprovedVipPurchaseOnce } from "./checkoutSuccessTracking.js";

const PAYMENT_POLL_DELAY_MS = 1500;
const PAYMENT_POLL_ATTEMPTS = 20;
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

function normalizePaymentStatus(payload = {}) {
  return String(payload.payment?.status || payload.status || "")
    .trim()
    .toLowerCase();
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

async function getApprovedPayment(paymentId) {
  let lastPayload = null;

  for (let attempt = 0; attempt < PAYMENT_POLL_ATTEMPTS; attempt += 1) {
    const { response, payload } = await getPayment(paymentId);
    lastPayload = payload;

    const paymentStatus = normalizePaymentStatus(payload);
    if (response.ok && payload.payment && paymentStatus === "approved") {
      return payload.payment;
    }

    if (response.ok && PROCESSING_PAYMENT_STATUSES.has(paymentStatus)) {
      if (attempt < PAYMENT_POLL_ATTEMPTS - 1) {
        await wait(PAYMENT_POLL_DELAY_MS);
        continue;
      }

      throw new ProcessingPaymentError();
    }

    throw new Error(resolvePaymentErrorMessage(paymentStatus, payload));
  }

  if (PROCESSING_PAYMENT_STATUSES.has(normalizePaymentStatus(lastPayload))) {
    throw new ProcessingPaymentError();
  }

  throw new Error(lastPayload?.error || "No se pudo consultar el pago.");
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
    persistDplridFromCustomerEmail(getPaymentCustomerEmail(payment));
    updateCheckoutEvents();
    successView.setContainerVisible(true);
    trackApprovedVipPurchaseOnce(paymentId, payment);
  } catch (error) {
    successView.showErrorState(error.message || "No se pudo consultar el pago.", error.title);
  } finally {
    successView.setSpinnerVisible(false);
  }
}
