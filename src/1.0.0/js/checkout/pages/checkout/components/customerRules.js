import { STEPS } from "../state/checkoutState.js";
import { hasRequiredCustomerData } from "../state/checkoutSelectors.js";

export function resolveCustomerMode(customerProfile, customerData = {}) {
  if (customerProfile?.isVip) {
    return "vip";
  }

  if (customerProfile?.exists) {
    if (!customerProfile.isFree) {
      return "recognized_incomplete";
    }

    return hasRequiredCustomerData(customerData) ? "recognized_complete" : "recognized_incomplete";
  }

  return "anonymous_editing";
}

function shouldSkipCustomerDataStep(profileResult = {}) {
  return profileResult.mode === "vip" || profileResult.mode === "recognized_complete";
}

export function resolveNextStepAfterCustomerProfile(state = {}, profileResult = {}) {
  if (shouldSkipCustomerDataStep(profileResult)) {
    return STEPS.PAYMENT;
  }

  return state.currentStep === STEPS.IDENTIFICATION ? STEPS.CUSTOMER_DATA : state.currentStep;
}
