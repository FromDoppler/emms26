const DEFAULT_REQUEST_TIMEOUT_MS = 15000;

async function readJsonResponse(response) {
  try {
    return await response.json();
  } catch (error) {
    return {
      error: response.ok ? "invalid_json_response" : "server_error",
      status: response.status,
    };
  }
}

async function fetchJsonWithTimeout(url, options = {}) {
  if (typeof AbortController !== "function") {
    return fetch(url, options);
  }

  const controller = new AbortController();
  const timeoutId = window.setTimeout(() => controller.abort(), DEFAULT_REQUEST_TIMEOUT_MS);

  try {
    return await fetch(url, {
      ...options,
      signal: controller.signal,
    });
  } finally {
    window.clearTimeout(timeoutId);
  }
}

export async function calculatePayment(payload) {
  const response = await fetchJsonWithTimeout("/services/calculate-payment.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  return {
    response,
    payload: await readJsonResponse(response),
  };
}

export async function createPayment(payload) {
  const response = await fetchJsonWithTimeout("/services/create-payment.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  return {
    status: response.status,
    payload: await readJsonResponse(response),
  };
}
