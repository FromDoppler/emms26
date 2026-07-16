import { setStatusMessage } from "./checkoutComponents.js";
import { hasFreshPricing, isSubmitContextAllowed, STEPS } from "../state/checkoutSelectors.js";
import { canSubmitCheckout } from "../checkoutRules.js";

export function renderPaymentView(state, view, snapshot = {}) {
  const isPaymentStep = state.currentStep === STEPS.PAYMENT;
  const shouldShowEprotect = isPaymentStep && state.eprotectVisible;

  const isEprotectLoading = shouldShowEprotect && state.eprotectLoading;

  view.eprotectContainer.hidden = !shouldShowEprotect;
  view.eprotectContainer.setAttribute("aria-hidden", shouldShowEprotect ? "false" : "true");
  view.eprotectLoading.hidden = !isEprotectLoading;
  view.eprotectFrame.dataset.eprotectLoading = isEprotectLoading ? "true" : "false";

  setStatusMessage(view.paymentMethodStatus, state.paymentStatusMessage || "", state.paymentStatusIsError);

  const shouldShowSubmit = isPaymentStep && isSubmitContextAllowed(state);
  const isFree = Boolean(hasFreshPricing(state) && !state.pricing.requiresPayment);
  const isDisabled = state.paymentInFlight || !canSubmitCheckout(state, snapshot);

  view.submitButton.hidden = !shouldShowSubmit;
  view.submitButton.disabled = isDisabled;
  view.submitButton.textContent = isFree ? "Confirmar acceso VIP" : "Completar compra VIP";
  view.submitButton.setAttribute("aria-disabled", isDisabled ? "true" : "false");
}

export function createPaymentComponent(context) {
  const { store, view } = context;

  return {
    render(state = store.getState(), snapshot = {}) {
      renderPaymentView(state, view, snapshot);
    },
  };
}
