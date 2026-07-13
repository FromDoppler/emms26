function byId(id) {
  return document.getElementById(id);
}

function setElementTextById(id, value) {
  const element = byId(id);
  if (element) {
    element.textContent = value || "";
  }
}

function setElementHiddenById(id, hidden) {
  const element = byId(id);
  if (element) {
    element.hidden = hidden;
  }
}

function formatPaymentDate(value) {
  const text = String(value || "").trim();
  if (!text) {
    return "";
  }

  const parsedDate = new Date(text);
  if (!Number.isNaN(parsedDate.getTime())) {
    return new Intl.DateTimeFormat("es-AR", {
      dateStyle: "medium",
      timeStyle: "short",
    }).format(parsedDate);
  }

  return text;
}

function normalizePaymentReceipt(payment = {}) {
  const finalAmount = payment.finalAmount;
  const paymentMethod = String(payment.paymentMethod || "")
    .trim()
    .toLowerCase();
  const hasFinalAmount = finalAmount !== undefined && finalAmount !== null && finalAmount !== "";
  const normalizedFinalAmount = hasFinalAmount ? Number(finalAmount) : null;
  const isCouponPayment = paymentMethod === "coupon";
  const isFreePayment = !isCouponPayment && hasFinalAmount && Number.isFinite(normalizedFinalAmount) && normalizedFinalAmount <= 0;

  return {
    currency: payment.currency || "USD",
    finalAmount: Number.isFinite(normalizedFinalAmount) ? normalizedFinalAmount : null,
    isCouponPayment,
    isFreePayment,
    paymentMethod,
  };
}

function getPaymentMethodLabel(receipt) {
  if (receipt.isCouponPayment) {
    return "Cupón 100%";
  }

  if (receipt.isFreePayment) {
    return "Sin cargo";
  }

  return "Tarjeta";
}

function getPaymentAmountLabel(receipt) {
  if (receipt.isCouponPayment || receipt.isFreePayment) {
    return "Sin cargo";
  }

  return receipt.finalAmount === null ? "Monto no informado" : `${receipt.currency} ${receipt.finalAmount.toFixed(2)}`;
}

export function getPaymentCustomerEmail(payment) {
  return payment.customerEmail || "";
}

export function renderSuccessReceipt(payment) {
  const receipt = normalizePaymentReceipt(payment);

  setElementTextById("ticketName", payment.ticketName || "VIP");
  setElementTextById("customerName", payment.customerName || "");
  setElementTextById("paymentMethod", getPaymentMethodLabel(receipt));
  setElementTextById("date", formatPaymentDate(payment.createdAt));
  setElementTextById("amount", getPaymentAmountLabel(receipt));
  setElementHiddenById("error-state", true);
  setElementHiddenById("success", false);

  const successCard = document.querySelector(".emms__checkout__success-card");
  if (successCard) {
    successCard.setAttribute("data-payment-method", receipt.isCouponPayment ? "coupon" : receipt.isFreePayment ? "free" : "card");
  }
}
