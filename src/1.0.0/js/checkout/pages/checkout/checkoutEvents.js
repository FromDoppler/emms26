export function bindCheckoutEvents({ view, flow }) {
  view.resolveCustomerButton.addEventListener("click", function () {
    flow.resolveCustomerEmail();
  });

  view.emailInput.addEventListener("input", function () {
    flow.handleEmailInput();
  });

  [view.nameInput, view.phoneInput].forEach((element) => {
    element.addEventListener("input", function () {
      flow.handleProfileInput(element);
    });
  });

  [view.policiesCheckbox, view.promotionsCheckbox].forEach((element) => {
    element.addEventListener("change", function () {
      flow.handleConsentChange(element);
    });
  });

  view.customerNextStepButton.addEventListener("click", function () {
    flow.advanceFromCustomerStep();
  });

  view.ticketSelect.addEventListener("change", function () {
    flow.handleTicketChange();
  });

  view.couponToggleButton.addEventListener("click", function () {
    flow.toggleCouponEditor();
  });

  view.couponInput.addEventListener("input", function () {
    flow.handleCouponInput();
  });

  view.applyCouponButton.addEventListener("click", function () {
    flow.applyCoupon();
  });

  view.removeCouponButton.addEventListener("click", function () {
    flow.removeCoupon();
  });

  if (view.couponStatusDismissButton) {
    view.couponStatusDismissButton.addEventListener("click", function () {
      flow.dismissCouponStatus();
    });
  }

  view.stepEditButtons.forEach((button) => {
    button.addEventListener("click", function () {
      flow.handleEditStep(button.dataset.stepEdit);
    });
  });

  view.submitButton.addEventListener("click", function () {
    flow.handleSubmit();
  });
}
