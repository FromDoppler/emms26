import { requestJsonWithTimeout } from "./paymentHttpClient.js";

export async function calculatePayment(payload) {
  const {
    response,
    payload: jsonPayload,
    parseError,
  } = await requestJsonWithTimeout("/services/calculate-payment.php", {
    method: "POST",
    redirect: "error",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  return {
    response,
    payload: jsonPayload,
    parseError,
  };
}

export async function createPayment(payload) {
  const body = typeof payload === "string" ? payload : JSON.stringify(payload);
  const {
    response,
    payload: jsonPayload,
    parseError,
  } = await requestJsonWithTimeout("/services/create-payment.php", {
    method: "POST",
    redirect: "error",
    headers: { "Content-Type": "application/json" },
    body,
  });

  return {
    status: response.status,
    payload: jsonPayload,
    parseError,
  };
}

export async function getPayment(paymentId) {
  const { response, payload, parseError } = await requestJsonWithTimeout(`/services/get-payment.php?payment_id=${encodeURIComponent(paymentId)}`, {
    method: "GET",
    headers: { Accept: "application/json" },
    redirect: "error",
    cache: "no-store",
  });

  return { status: response.status, payload, parseError };
}
