async function readJsonResponse(response) {
  try {
    return await response.json();
  } catch (error) {
    return {
      error: response.ok ? "invalid_json_response" : "server_error",
    };
  }
}

export async function getPayment(paymentId) {
  const response = await fetch(`/services/get-payment.php?payment_id=${encodeURIComponent(paymentId)}`, {
    cache: "no-store",
  });

  return {
    response,
    payload: await readJsonResponse(response),
  };
}
