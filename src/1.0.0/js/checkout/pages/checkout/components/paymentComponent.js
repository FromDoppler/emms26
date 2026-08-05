import { setStatusMessage } from "./checkoutComponents.js";
import { hasFreshPricing, isSubmitContextAllowed, STEPS } from "../state/checkoutSelectors.js";
import { canSubmitCheckout } from "../checkoutRules.js";

export function renderPaymentView(state, view, snapshot = {}) {
  const isPaymentStep = state.currentStep === STEPS.PAYMENT;
  const hasActiveAttempt = Boolean(state.activePaymentId);
  const isPaymentLocked = Boolean(state.paymentInFlight || hasActiveAttempt);
  const shouldShowEprotect = isPaymentStep && state.eprotectVisible && !hasActiveAttempt;

  const isEprotectLoading = shouldShowEprotect && state.eprotectLoading;

  view.eprotectContainer.hidden = !shouldShowEprotect;
  view.eprotectContainer.setAttribute("aria-hidden", shouldShowEprotect ? "false" : "true");
  if (isPaymentLocked) {
    view.eprotectContainer.setAttribute("inert", "");
  } else {
    view.eprotectContainer.removeAttribute("inert");
  }
  view.eprotectLoading.hidden = !isEprotectLoading;
  view.eprotectFrame.dataset.eprotectLoading = isEprotectLoading ? "true" : "false";
  view.eprotectFrame.dataset.paymentLocked = isPaymentLocked ? "true" : "false";

  setStatusMessage(view.paymentMethodStatus, state.paymentStatusMessage || "", state.paymentStatusIsError);

  const shouldShowSubmit = isPaymentStep && (hasActiveAttempt || isSubmitContextAllowed(state));
  const isFree = Boolean(hasFreshPricing(state) && !state.pricing.requiresPayment);
  const isDisabled = state.paymentInFlight || (!hasActiveAttempt && !canSubmitCheckout(state, snapshot));

  view.submitButton.hidden = !shouldShowSubmit;
  view.submitButton.disabled = isDisabled;
  view.submitButton.textContent = hasActiveAttempt ? "Volver a verificar el pago" : isFree ? "Confirmar acceso VIP" : "Completar compra VIP";
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
