import { getUtmParams } from "./checkoutUrl.js";

export function getCustomerPayload(state, snapshot = {}) {
  return {
    name: state.customerData.name || snapshot.name || "",
    email: state.customerData.email || snapshot.email || "",
    phone: state.customerData.phone || snapshot.phone || "",
    acceptPolicies: Boolean(snapshot.acceptPolicies),
    acceptPromotions: Boolean(snapshot.acceptPromotions),
    ...getUtmParams(state.origin),
  };
}

export function getCouponCode(state, snapshot = {}, options = {}) {
  const explicitCouponCode = String(options.couponCode || "").trim();

  if (explicitCouponCode) {
    return explicitCouponCode;
  }

  return String(state.resolvedCouponCode || state.urlCouponCode || "").trim();
}

export function buildCalculatePayload(state, snapshot = {}, options = {}) {
  const couponCode = getCouponCode(state, snapshot, options);
  const ticketCode = state.selectedTicketCode || snapshot.selectedTicketCode || "";
  const customerEmail = state.customerData.email || snapshot.email || "";

  return {
    origin: state.origin,
    ...(couponCode ? { couponCode } : {}),
    ...(ticketCode ? { ticketCode } : {}),
    ...(customerEmail ? { customerEmail } : {}),
  };
}

export function buildCreatePaymentPayload(state, snapshot = {}, paymentPayload = {}, customerPayload = null) {
  return {
    checkout: {
      idempotencyKey: state.idempotencyKey,
      origin: state.origin,
    },
    customer: customerPayload || getCustomerPayload(state, snapshot),
    ticketCode: (state.pricing && state.pricing.ticket && state.pricing.ticket.code) || state.selectedTicketCode || null,
    couponCode: state.resolvedCouponCode,
    payment: paymentPayload,
  };
}
