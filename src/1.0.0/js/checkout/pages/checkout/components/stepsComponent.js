import { setElementText } from "./checkoutComponents.js";
import { getCustomerDataSummary, getIdentificationSummary, getPaymentSummary, getStepState, isPaymentStepResolvedWithoutCard, STEP_ORDER, STEPS } from "../state/checkoutSelectors.js";
import { isCustomerReadyForPayment } from "../checkoutRules.js";

function renderStepState(element, stateValue) {
  element.dataset.stepState = stateValue;

  const marker = element.querySelector("[data-step-marker], [data-step-card-marker]");
  if (!marker) {
    return;
  }

  if (stateValue === "complete") {
    marker.textContent = "✓";
    return;
  }

  const step = element.dataset.checkoutStepperItem || element.dataset.checkoutStepCard;
  marker.textContent = String(STEP_ORDER.indexOf(step) + 1);
}

function renderSummaryText(element, summary) {
  if (!element) {
    return;
  }

  element.classList.remove("emms__checkout__step-summary--customer");

  if (summary && summary.type === "lines") {
    element.classList.add("emms__checkout__step-summary--customer");
    element.innerHTML = "";
    summary.lines.forEach((line) => {
      const span = document.createElement("span");
      const text = typeof line === "string" ? line : line?.text;
      const lineType = typeof line === "string" ? "" : line?.type;

      if (lineType) {
        span.classList.add(`emms__checkout__step-summary-${lineType}`);
      }

      span.textContent = text || "";
      element.appendChild(span);
    });
    return;
  }

  setElementText(element, typeof summary === "string" ? summary : summary?.text || "");
}

export function renderStepsView(state, view, snapshot = {}) {
  const customerReadyForPayment = isCustomerReadyForPayment(state, snapshot);

  view.stepperItems.forEach((item) => {
    renderStepState(item, getStepState(state, item.dataset.checkoutStepperItem));
  });

  view.stepCards.forEach((card) => {
    const step = card.dataset.checkoutStepCard;
    const stepState = getStepState(state, step);
    renderStepState(card, stepState);
    const body = card.querySelector("[data-step-card-body]");
    if (body) {
      const isResolvedPaymentStep = step === STEPS.PAYMENT && isPaymentStepResolvedWithoutCard(state);
      body.hidden = stepState !== "active" || isResolvedPaymentStep;
    }
  });

  view.stepSummaries.forEach((summaryElement) => {
    const step = summaryElement.dataset.stepSummary;

    if (step === "identification") {
      renderSummaryText(summaryElement, getIdentificationSummary(state));
      return;
    }

    if (step === "customer-data") {
      renderSummaryText(summaryElement, getCustomerDataSummary(state, customerReadyForPayment));
      return;
    }

    if (step === "payment") {
      renderSummaryText(summaryElement, getPaymentSummary(state));
    }
  });

  const canEditCompletedSteps = state.currentStep === STEPS.PAYMENT && state.customerMode !== "vip";

  view.stepEditButtons.forEach((button) => {
    const step = button.dataset.stepEdit;
    const stateValue = getStepState(state, step);
    button.hidden = !canEditCompletedSteps || stateValue !== "complete";
  });
}

export function createStepsComponent({ store, view }) {
  return {
    render(state = store.getState(), snapshot = {}) {
      renderStepsView(state, view, snapshot);
    },
  };
}
