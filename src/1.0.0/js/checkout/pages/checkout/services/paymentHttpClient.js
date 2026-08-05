const DEFAULT_REQUEST_TIMEOUT_MS = 15000;

async function readJsonResponse(response) {
  try {
    return {
      payload: await response.json(),
      parseError: false,
    };
  } catch (error) {
    if (error && error.name === "AbortError") {
      throw error;
    }

    return {
      payload: null,
      parseError: true,
    };
  }
}

export async function requestJsonWithTimeout(url, options = {}) {
  if (typeof AbortController !== "function") {
    throw new Error("abort_controller_required");
  }

  const controller = new AbortController();
  const timeoutId = window.setTimeout(() => controller.abort(), DEFAULT_REQUEST_TIMEOUT_MS);

  try {
    const response = await fetch(url, {
      ...options,
      signal: controller.signal,
    });

    const parsedResponse = await readJsonResponse(response);

    return {
      response,
      payload: parsedResponse.payload,
      parseError: parsedResponse.parseError,
    };
  } finally {
    window.clearTimeout(timeoutId);
  }
}
