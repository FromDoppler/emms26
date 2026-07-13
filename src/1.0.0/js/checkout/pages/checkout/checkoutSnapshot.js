import { normalizeEmail } from "./checkoutValidation.js";

function getElementValue(element) {
  return String(element?.value || "").trim();
}

function readPhoneNumber(state = {}, view = {}) {
  if (state.phoneControl && typeof state.phoneControl.getNumber === "function") {
    return String(state.phoneControl.getNumber() || "").trim();
  }

  return getElementValue(view.phoneInput);
}

function isPhoneNumberValid(state = {}) {
  if (!state.phoneControl || typeof state.phoneControl.isValid !== "function") {
    return false;
  }

  return Boolean(state.phoneControl.isValid());
}

export function readCheckoutSnapshot(view, state = {}) {
  return {
    email: normalizeEmail(state.customerData?.email || getElementValue(view.emailInput)),
    name: getElementValue(view.nameInput),
    phone: readPhoneNumber(state, view),
    couponCode: getElementValue(view.couponInput),
    selectedTicketCode: getElementValue(view.ticketSelect),
    acceptPolicies: Boolean(view.policiesCheckbox?.checked),
    acceptPromotions: Boolean(view.promotionsCheckbox?.checked),
    phoneInputReadOnly: Boolean(view.phoneInput?.readOnly),
    phoneInputReady: Boolean(state.phoneInputReady && !state.phoneInputFailed),
    phoneInputValid: isPhoneNumberValid(state),
  };
}
