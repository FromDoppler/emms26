import { getCustomerIdentity } from "../../checkoutStorage.js";
import { bindCheckoutEvents } from "./checkoutEvents.js";
import { normalizeEmail } from "./checkoutValidation.js";
import { getUrlCouponCode, removeCouponCodeParamFromUrl } from "./checkoutUrl.js";
import { getMissingEprotectFields } from "./adapters/eprotectAdapter.js";
import { createEprotectService } from "./services/eprotectService.js";
import { createPricingService } from "./services/pricingService.js";
import { createSubmitService } from "./services/submitService.js";
import { clearValidationFeedback, focusFirstInvalidField, showValidationErrors, setCouponStatusMessage, setStatusMessage, setSpinnerVisible } from "./components/checkoutComponents.js";
import { createCustomerComponent } from "./components/customerComponent.js";
import { createPaymentComponent } from "./components/paymentComponent.js";
import { createStepsComponent } from "./components/stepsComponent.js";
import { createSummaryComponent } from "./components/summaryComponent.js";
import { resolveNextStepAfterCustomerProfile } from "./components/customerRules.js";
import { hasFreshPricing, shouldMountEprotect, STEPS } from "./state/checkoutSelectors.js";
import { buildCalculatePayload } from "./checkoutPayloads.js";
import { readCheckoutSnapshot } from "./checkoutSnapshot.js";
import { isCouponError, resolveCheckoutErrorMessage, resolveCouponErrorMessage } from "./checkoutMessages.js";
import { validateCheckoutForSubmit } from "./checkoutRules.js";
import { rotateIdempotencyKey } from "./state/checkoutState.js";

function getValidationTargets(view) {
  return {
    customerEmailStatus: view.customerEmailStatus,
    customerNameStatus: view.customerNameStatus,
    customerPhoneStatus: view.customerPhoneStatus,
    customerPoliciesStatus: view.customerPoliciesStatus,
    emailInput: view.emailInput,
    nameInput: view.nameInput,
    phoneInput: view.phoneInput,
    policiesCheckbox: view.policiesCheckbox,
    paymentMethodStatus: view.paymentMethodStatus,
    checkoutStatus: view.checkoutStatus,
  };
}

export function createCheckoutFlow({ store, view }) {
  let pageComponents;
  let pricing;
  let eprotect;
  let submit;

  const readSnapshot = () => readCheckoutSnapshot(view, store.getState());

  const render = () => {
    const state = store.getState();
    const snapshot = readCheckoutSnapshot(view, state);
    pageComponents.customer.render(state, snapshot);
    pageComponents.steps.render(state, snapshot);
    pageComponents.summary.render(state, snapshot);
    pageComponents.payment.render(state, snapshot);
  };

  function initializeCheckoutState() {
    const identity = getCustomerIdentity();
    const email = normalizeEmail(identity.email);
    const couponCode = getUrlCouponCode();

    store.dispatch({
      type: "CHECKOUT_INITIALIZED",
      email,
      couponCode,
    });

    view.emailInput.value = email;
    if (couponCode) {
      view.couponInput.value = couponCode;
    }

    rotateIdempotencyKey(store);
  }

  function rotatePurchaseIntent() {
    rotateIdempotencyKey(store);
    eprotect.teardown();
  }

  function applyCustomerProfile(customerProfile) {
    const profileResult = pageComponents.customer.resolveCustomerProfile(customerProfile);
    if (!profileResult) {
      return false;
    }

    const state = store.getState();
    store.dispatch({
      type: "CUSTOMER_PROFILE_APPLIED",
      ...profileResult,
      currentStep: resolveNextStepAfterCustomerProfile(state, profileResult),
    });

    return true;
  }

  async function syncPaymentForCurrentStep() {
    const state = store.getState();

    if (state.currentStep !== STEPS.PAYMENT) {
      return false;
    }

    if (shouldMountEprotect(state)) {
      await eprotect.mount();
      return true;
    }

    eprotect.teardown();

    if (hasFreshPricing(state) && !state.pricing.requiresPayment && state.customerMode !== "vip") {
      store.dispatch({
        type: "PAYMENT_STATUS_CHANGED",
        message: "",
        isError: false,
      });
      return false;
    }

    store.dispatch({
      type: "PAYMENT_STATUS_CHANGED",
      message: "",
      isError: false,
    });
    return false;
  }

  async function calculatePricing(options = {}) {
    setCouponStatusMessage(view.couponStatus, "");
    setStatusMessage(view.checkoutStatus, "");

    setSpinnerVisible(view, true);

    try {
      const result = await pricing.calculate(options);

      if (result.customerProfile !== undefined) {
        applyCustomerProfile(result.customerProfile);
      }

      if (result.skipPricingForVip) {
        store.dispatch({ type: "PRICING_SKIPPED_FOR_VIP" });
        setCouponStatusMessage(view.couponStatus, "");
        await syncPaymentForCurrentStep();
        render();
        return true;
      }

      if (result.ok) {
        setCouponStatusMessage(view.couponStatus, "");
        setStatusMessage(view.checkoutStatus, "");
      } else if (result.error && isCouponError(result.error)) {
        const failedCouponCode = pageComponents.summary.discardFailedCouponAttempt() || options.couponCode || "";
        removeCouponCodeParamFromUrl();
        setCouponStatusMessage(view.couponStatus, resolveCouponErrorMessage(result.error, failedCouponCode), "error");
      } else if (result.error) {
        setStatusMessage(view.checkoutStatus, resolveCheckoutErrorMessage(result.error), true);
        setCouponStatusMessage(view.couponStatus, "");
      } else {
        setCouponStatusMessage(view.couponStatus, "");
      }

      await syncPaymentForCurrentStep();
      render();
      return Boolean(result.ok);
    } finally {
      if (!store.getState().pricingLoading) {
        setSpinnerVisible(view, false);
      }
    }
  }

  function resolveCustomerEmail() {
    const accepted = pageComponents.customer.resolveCustomerEmail();
    if (!accepted) {
      return;
    }

    rotatePurchaseIntent();
    calculatePricing();
  }

  function handleEmailInput() {
    pageComponents.summary.clearCouponFeedback();
    pageComponents.customer.handleEmailInput();
  }

  function handleProfileInput(element) {
    pageComponents.summary.clearCouponFeedback();
    pageComponents.customer.handleProfileInput(element);
    render();
  }

  function handleConsentChange(element) {
    pageComponents.summary.clearCouponFeedback();
    pageComponents.customer.handleConsentChange(element);
    render();
  }

  function handlePhoneInputChanged() {
    pageComponents.summary.clearCouponFeedback();
    render();
  }

  async function advanceFromCustomerStep() {
    pageComponents.customer.syncEditableCustomerFields();
    const snapshot = readSnapshot();
    clearValidationFeedback(view);

    const errors = pageComponents.customer.validateCustomerStep(snapshot);
    if (errors.length > 0) {
      showValidationErrors(errors);
      focusFirstInvalidField(errors);
      return;
    }

    let pricingReady = hasFreshPricing(store.getState());
    if (!pricingReady) {
      pricingReady = await calculatePricing();
    }

    if (!pricingReady) {
      render();
      return;
    }

    store.dispatch({
      type: "STEP_REQUESTED",
      step: STEPS.PAYMENT,
    });
    await syncPaymentForCurrentStep();
  }

  function handleEditStep(step) {
    pageComponents.summary.clearCouponFeedback();

    if (step === STEPS.IDENTIFICATION) {
      pageComponents.customer.startIdentificationEditing();
      render();
      return;
    }

    if (step === STEPS.CUSTOMER_DATA) {
      pageComponents.customer.startCustomerEditing();
      render();
    }
  }

  function handleTicketChange() {
    pageComponents.summary.handleTicketChange();
    rotatePurchaseIntent();
    calculatePricing();
  }

  function toggleCouponEditor() {
    pageComponents.summary.toggleCouponEditor();
    render();
  }

  function handleCouponInput() {
    pageComponents.summary.handleCouponInput();
    render();
  }

  function applyCoupon() {
    const couponCode = pageComponents.summary.readCouponDraft();
    if (!couponCode) {
      setCouponStatusMessage(view.couponStatus, "Ingresá un código para aplicarlo.", "warning");
      view.couponInput.focus();
      return;
    }

    pageComponents.summary.prepareCouponApply();
    rotatePurchaseIntent();
    calculatePricing({ couponCode });
  }

  function removeCoupon() {
    pageComponents.summary.removeCoupon();
    removeCouponCodeParamFromUrl();
    rotatePurchaseIntent();
    calculatePricing();
  }

  function dismissCouponStatus() {
    setCouponStatusMessage(view.couponStatus, "");
  }

  async function handleSubmit() {
    pageComponents.customer.syncEditableCustomerFields();
    pageComponents.summary.clearCouponDraft();
    const snapshot = readSnapshot();
    const customerPayload = pageComponents.customer.collectCustomerPayload(snapshot);
    clearValidationFeedback(view);

    const validationErrors = validateCheckoutForSubmit(store.getState(), snapshot, getValidationTargets(view), {
      hasMissingEprotectConfig: getMissingEprotectFields().length > 0,
    });
    if (validationErrors.length > 0) {
      showValidationErrors(validationErrors);
      focusFirstInvalidField(validationErrors);
      return;
    }

    await submit.submit({ customerPayload, snapshot });
    render();
  }

  const flow = {
    advanceFromCustomerStep,
    applyCoupon,
    calculatePricing,
    dismissCouponStatus,
    handleConsentChange,
    handleCouponInput,
    handleEditStep,
    handleEmailInput,
    handlePhoneInputChanged,
    handleProfileInput,
    handleSubmit,
    handleTicketChange,
    init,
    removeCoupon,
    resolveCustomerEmail,
    toggleCouponEditor,
  };

  function init() {
    const context = { store, view, render };

    pageComponents = {
      customer: createCustomerComponent(context),
      summary: createSummaryComponent(context),
      payment: createPaymentComponent(context),
      steps: createStepsComponent(context),
    };

    eprotect = createEprotectService({ store, payframeElement: view.eprotectPayframe });
    submit = createSubmitService({ store, view, eprotect });
    pricing = createPricingService({
      store,
      buildPayload: (state, options = {}) => buildCalculatePayload(state, readSnapshot(), options),
    });

    store.subscribe(render);
    pageComponents.customer.initializePhoneInput(flow.handlePhoneInputChanged);
    initializeCheckoutState();
    bindCheckoutEvents({ view, flow });
    render();
    flow.calculatePricing();
  }

  return flow;
}
