import { STEP_ORDER, STEPS } from "./checkoutState.js";
import { isValidEmail, normalizeEmail } from "../checkoutValidation.js";

export { STEP_ORDER, STEPS, isValidEmail, normalizeEmail };

export function isVipMode(state) {
  return state.customerMode === "vip" || Boolean(state.customerData?.isVip);
}

export function hasFreshPricing(state) {
  return Boolean(state.pricing && !state.pricingStale);
}

export function isEditableCustomerMode(state) {
  return state.customerMode === "anonymous_editing" || state.customerMode === "recognized_incomplete";
}

export function isPhoneInputReady(state) {
  return Boolean(state.phoneControl && state.phoneInputReady && !state.phoneInputFailed);
}

export function hasRequiredCustomerData(customerData = {}) {
  return Boolean(customerData.email && customerData.name && customerData.phone);
}

export function shouldAskConsents(customerProfile) {
  return !(customerProfile && (customerProfile.isFree || customerProfile.isVip));
}

export function shouldShowPaymentSection(state) {
  return Boolean(hasFreshPricing(state) && state.pricing.requiresPayment && state.customerMode !== "email" && state.customerMode !== "vip" && state.customerData.email);
}

export function shouldMountEprotect(state) {
  return state.currentStep === STEPS.PAYMENT && shouldShowPaymentSection(state);
}

export function isPaymentStepResolvedWithoutCard(state) {
  return Boolean(hasFreshPricing(state) && !state.pricing.requiresPayment && state.customerMode !== "email" && state.customerMode !== "vip");
}

export function isSubmitContextAllowed(state) {
  return Boolean(state.customerMode !== "email" && state.customerMode !== "vip" && hasFreshPricing(state));
}

export function getStepIndex(step) {
  return STEP_ORDER.indexOf(step);
}

export function getStepState(state, step) {
  const currentIndex = getStepIndex(state.currentStep);
  const index = getStepIndex(step);

  if (isVipMode(state) || index < currentIndex) {
    return "complete";
  }

  if (index === currentIndex) {
    return "active";
  }

  return "locked";
}

export function getIdentificationSummary(state) {
  return state.customerData.email || "Ingresá tu email para continuar.";
}

export function getCustomerDataSummary(state, customerReadyForPayment = false) {
  if (state.customerMode === "vip") {
    return {
      type: "text",
      text: "Datos ya asociados a tu acceso VIP.",
    };
  }

  if (customerReadyForPayment) {
    if (state.currentStep === STEPS.CUSTOMER_DATA) {
      return {
        type: "text",
        text: "Datos completos.",
      };
    }

    return {
      type: "lines",
      lines: [
        { type: "name", text: state.customerData.name },
        { type: "phone", text: getCustomerDisplayPhone(state) },
      ].filter((line) => Boolean(line.text)),
    };
  }

  if (state.customerMode === "email") {
    return {
      type: "text",
      text: "Pendiente de identificación.",
    };
  }

  return {
    type: "text",
    text: "Completá nombre, teléfono y política de privacidad.",
  };
}

export function getCustomerDisplayPhone(state) {
  if (typeof state.phoneControl?.getDisplayNumber === "function") {
    const displayPhone = String(state.phoneControl.getDisplayNumber() || "").trim();

    if (displayPhone) {
      return displayPhone;
    }
  }

  return String(state.customerData.phone || "").trim();
}

export function getPaymentSummary(state) {
  if (state.customerMode === "vip") {
    return "Acceso ya confirmado.";
  }

  if (hasFreshPricing(state) && !state.pricing.requiresPayment && state.customerMode !== "vip") {
    return "Pago bonificado. No requiere tarjeta.";
  }

  if (hasFreshPricing(state) && state.pricing.requiresPayment) {
    return "Completá la tarjeta para finalizar.";
  }

  return "Se habilita después de tus datos.";
}
