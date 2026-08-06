import { requestJsonWithTimeout } from "../checkout/services/paymentHttpClient.js";

export async function getPayment(paymentId) {
  const { response, payload, parseError } = await requestJsonWithTimeout(`/services/get-payment.php?payment_id=${encodeURIComponent(paymentId)}`, {
    redirect: "error",
    cache: "no-store",
    headers: { Accept: "application/json" },
  });

  return { response, status: response.status, payload, parseError };
}
