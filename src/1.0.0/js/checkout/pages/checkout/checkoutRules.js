import { isValidEmail } from "./checkoutValidation.js";
import { hasFreshPricing, hasRequiredCustomerData } from "./state/checkoutSelectors.js";

function isEditableCustomerMode(state = {}) {
  return state.customerMode === "anonymous_editing" || state.customerMode === "recognized_incomplete";
}

export function isPhoneInputEditable(state = {}, snapshot = {}) {
  if (!isEditableCustomerMode(state)) {
    return false;
  }

  return !snapshot.phoneInputReadOnly;
}

export function isPhoneInputUsable(state = {}, snapshot = {}) {
  if (!isPhoneInputEditable(state, snapshot)) {
    return true;
  }

  return Boolean(snapshot.phoneInputReady);
}

export function isCurrentPhoneValid(state = {}, snapshot = {}) {
  if (!isPhoneInputEditable(state, snapshot)) {
    return true;
  }

  return Boolean(snapshot.phoneInputReady && snapshot.phoneInputValid);
}

export function hasRequiredConsents(state = {}, snapshot = {}) {
  return Boolean(state.customerData?.isFree || state.customerData?.isVip || snapshot.acceptPolicies);
}

export function isCustomerReadyForPayment(state = {}, snapshot = {}) {
  return Boolean(state.customerMode !== "email" && hasRequiredCustomerData(state.customerData) && hasRequiredConsents(state, snapshot) && isCurrentPhoneValid(state, snapshot));
}

export function canSubmitCheckout(state = {}, snapshot = {}) {
  if (state.customerMode === "email" || state.customerMode === "vip" || state.paymentInFlight || !hasFreshPricing(state)) {
    return false;
  }

  if (!isCustomerReadyForPayment(state, snapshot)) {
    return false;
  }

  if (state.pricing?.requiresPayment) {
    return Boolean(state.eprotectClient && state.eprotectReady);
  }

  return true;
}

export function validateCustomerStep(state = {}, snapshot = {}, targets = {}) {
  const errors = [];

  if (state.customerMode === "email") {
    errors.push({
      message: "Ingresá tu email y presioná Continuar.",
      target: targets.customerEmailStatus,
      element: targets.emailInput,
    });
    return errors;
  }

  if (!snapshot.email) {
    errors.push({
      message: "El email es obligatorio.",
      target: targets.customerEmailStatus,
      element: targets.emailInput,
    });
  } else if (!isValidEmail(snapshot.email)) {
    errors.push({
      message: "Ingresá un email válido.",
      target: targets.customerEmailStatus,
      element: targets.emailInput,
    });
  }

  if (!state.customerData?.name && !snapshot.name) {
    errors.push({
      message: "El nombre es obligatorio.",
      target: targets.customerNameStatus,
      element: targets.nameInput,
    });
  }

  if (!state.customerData?.phone && !snapshot.phone) {
    errors.push({
      message: "El teléfono es obligatorio.",
      target: targets.customerPhoneStatus,
      element: targets.phoneInput,
    });
  } else if (isPhoneInputEditable(state, snapshot)) {
    if (!isPhoneInputUsable(state, snapshot)) {
      errors.push({
        message: "El selector de teléfono todavía no está listo.",
        target: targets.customerPhoneStatus,
        element: targets.phoneInput,
      });
    } else if (!isCurrentPhoneValid(state, snapshot)) {
      errors.push({
        message: "Ingresá un teléfono válido con código de país.",
        target: targets.customerPhoneStatus,
        element: targets.phoneInput,
      });
    }
  }

  if (!hasRequiredConsents(state, snapshot)) {
    errors.push({
      message: "Debés aceptar la política de privacidad.",
      target: targets.customerPoliciesStatus,
      element: targets.policiesCheckbox,
    });
  }

  return errors;
}

export function validateCheckoutForSubmit(state = {}, snapshot = {}, targets = {}, options = {}) {
  const errors = [];

  if (state.customerMode === "vip") {
    errors.push({
      message: "Ya tenés acceso VIP para este evento.",
      target: targets.checkoutStatus,
    });
    return errors;
  }

  errors.push(...validateCustomerStep(state, snapshot, targets));

  if (!state.pricing || state.pricingStale) {
    errors.push({
      message: "Completá los pasos previos para continuar.",
      target: targets.checkoutStatus,
    });
  } else if (state.pricing.requiresPayment && options.hasMissingEprotectConfig) {
    errors.push({
      message: "Checkout no disponible: faltan datos de configuración de pagos.",
      target: targets.paymentMethodStatus,
    });
  } else if (state.pricing.requiresPayment && (!state.eprotectClient || !state.eprotectReady)) {
    errors.push({
      message: "El iframe de pago seguro no está listo todavía.",
      target: targets.paymentMethodStatus,
    });
  }

  if (state.pricing && !state.selectedTicketCode && !state.pricing.ticket) {
    errors.push({
      message: "Seleccioná tu plan de acceso.",
      target: targets.checkoutStatus,
    });
  }

  return errors;
}
