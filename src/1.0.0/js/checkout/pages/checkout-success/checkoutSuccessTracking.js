const TRACKED_PURCHASE_STORAGE_PREFIX = "checkout_success_tracked:";

function safeLocalStorageGet(key) {
  try {
    return localStorage.getItem(key);
  } catch (error) {
    return null;
  }
}

function safeLocalStorageSet(key, value) {
  try {
    localStorage.setItem(key, value);
    return true;
  } catch (error) {
    return false;
  }
}

function normalizeAmount(value) {
  const amount = Number(value);
  return Number.isFinite(amount) ? amount : null;
}

function buildPurchasePayload(paymentId, payment = {}) {
  const value = normalizeAmount(payment.finalAmount);

  return {
    currency: payment.currency || "USD",
    ...(value !== null ? { value } : {}),
    content_name: payment.ticketName || "VIP",
    event_id: paymentId,
  };
}

export function trackApprovedVipPurchaseOnce(paymentId, payment = {}) {
  const normalizedPaymentId = String(paymentId || "").trim();
  if (!normalizedPaymentId) {
    return false;
  }

  const storageKey = `${TRACKED_PURCHASE_STORAGE_PREFIX}${normalizedPaymentId}`;
  if (safeLocalStorageGet(storageKey)) {
    return false;
  }

  try {
    const payload = buildPurchasePayload(normalizedPaymentId, payment);
    let tracked = false;

    if (typeof window.fbq === "function") {
      window.fbq("track", "EMMS_VIP");
      window.fbq("track", "Purchase", payload);
      tracked = true;
    }

    if (typeof window.gtag === "function") {
      window.gtag("event", "purchase", {
        transaction_id: normalizedPaymentId,
        currency: payload.currency,
        value: payload.value || 0,
        items: [
          {
            item_name: payload.content_name,
            quantity: 1,
          },
        ],
      });
      tracked = true;
    }

    if (tracked) {
      safeLocalStorageSet(storageKey, "1");
    }

    return tracked;
  } catch (error) {
    return false;
  }
}
