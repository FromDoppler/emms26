function getRequiredElement(root, selector, description = selector) {
  const element = root.querySelector(selector);
  if (!element) {
    throw new Error(`Missing checkout DOM node: ${description}`);
  }
  return element;
}

function getOptionalElement(root, selector) {
  return root.querySelector(selector);
}

function getAllElements(root, selector) {
  return Array.from(root.querySelectorAll(selector));
}

const COUPON_STATUS_TONE_CLASSES = ["emms__checkout__coupon-status--info", "emms__checkout__coupon-status--warning", "emms__checkout__coupon-status--error"];

export function setStatusMessage(element, text, isError = false) {
  if (!element) {
    return;
  }

  element.textContent = text || "";
  element.classList.toggle("emms__checkout__status--error", Boolean(text && isError));
}

function renderCouponStatusMessage(target, message) {
  target.replaceChildren();

  const lines = String(message || "")
    .split("\n")
    .map((line) => line.trim())
    .filter(Boolean);

  if (lines.length <= 1) {
    target.textContent = message || "";
    return;
  }

  const title = document.createElement("span");
  title.className = "emms__checkout__coupon-status-title";
  title.textContent = lines[0];
  target.appendChild(title);

  lines.slice(1).forEach((line) => {
    const code = document.createElement("strong");
    code.className = "emms__checkout__coupon-status-code";
    code.textContent = line;
    target.appendChild(code);
  });
}

export function setCouponStatusMessage(element, text, tone = "info") {
  if (!element) {
    return;
  }

  const message = text || "";
  const textTarget = element.querySelector("[data-coupon-status-text]");
  const dismissButton = element.querySelector("[data-coupon-status-dismiss]");

  element.classList.remove(...COUPON_STATUS_TONE_CLASSES);
  element.classList.toggle("emms__checkout__status--error", Boolean(message && tone === "error"));
  element.hidden = !message;

  if (textTarget) {
    renderCouponStatusMessage(textTarget, message);
  } else {
    element.textContent = message;
  }

  if (dismissButton) {
    dismissButton.hidden = !message;
  }

  const toneClass = `emms__checkout__coupon-status--${tone}`;
  if (message && COUPON_STATUS_TONE_CLASSES.includes(toneClass)) {
    element.classList.add(toneClass);
  }
}

export function setFieldInvalidState(element, isInvalid) {
  if (!element) {
    return;
  }

  element.setAttribute("aria-invalid", isInvalid ? "true" : "false");
}

export function setElementText(element, value) {
  if (element) {
    element.textContent = value || "";
  }
}

export function createCheckoutComponents(root) {
  return {
    root,
    spinner: getOptionalElement(root, "#spinner"),

    customerCard: getRequiredElement(root, "#customer-card", "customer card"),
    emailStep: getRequiredElement(root, "#customer-email-step", "customer email step"),
    emailInput: getRequiredElement(root, "#customer-email", "customer email input"),
    customerEmailStatus: getRequiredElement(root, "#customer-email-status", "customer email status"),
    resolveCustomerButton: getRequiredElement(root, "#resolve-customer", "resolve customer button"),

    profileSummary: getRequiredElement(root, "#customer-profile-summary", "customer profile summary"),
    customerSummaryTitle: getRequiredElement(root, "#customer-summary-title", "customer summary title"),
    customerSummaryCopy: getRequiredElement(root, "#customer-summary-copy", "customer summary copy"),
    customerSummaryName: getRequiredElement(root, "#customer-summary-name", "customer summary name"),
    customerSummaryPhone: getRequiredElement(root, "#customer-summary-phone", "customer summary phone"),
    customerFields: getRequiredElement(root, "#customer-fields", "customer fields"),
    nameInput: getRequiredElement(root, "#customer-name", "customer name input"),
    phoneInput: getRequiredElement(root, "#customer-phone", "customer phone input"),
    customerNameStatus: getRequiredElement(root, "#customer-name-status", "customer name status"),
    customerPhoneStatus: getRequiredElement(root, "#customer-phone-status", "customer phone status"),
    consentsWrapper: getRequiredElement(root, "#checkout-consents", "checkout consents"),
    policiesCheckbox: getRequiredElement(root, "#accept-policies", "policies checkbox"),
    promotionsCheckbox: getRequiredElement(root, "#accept-promotions", "promotions checkbox"),
    customerPoliciesStatus: getRequiredElement(root, "#customer-policies-status", "customer policies status"),
    customerStepActions: getRequiredElement(root, "#customer-step-actions", "customer step actions"),
    customerNextStepButton: getRequiredElement(root, "#customer-next-step", "customer next step button"),

    ticketSelect: getRequiredElement(root, "#ticket-code", "ticket selector"),
    ticketStatus: getRequiredElement(root, "#ticket-status", "ticket status"),
    couponSection: getRequiredElement(root, "#coupon-section", "coupon section"),
    couponToggleButton: getRequiredElement(root, "#coupon-toggle", "coupon toggle"),
    couponEditor: getRequiredElement(root, "#coupon-editor", "coupon editor"),
    couponInput: getRequiredElement(root, "#coupon-code", "coupon input"),
    applyCouponButton: getRequiredElement(root, "#apply-coupon", "apply coupon button"),
    couponApplied: getRequiredElement(root, "#coupon-applied", "coupon applied"),
    couponAppliedSource: getRequiredElement(root, "#coupon-applied-source", "coupon applied source"),
    couponAppliedCode: getRequiredElement(root, "#coupon-applied-code", "coupon applied code"),
    removeCouponButton: getRequiredElement(root, "#remove-coupon", "remove coupon button"),
    couponStatus: getRequiredElement(root, "#coupon-status", "coupon status"),
    couponStatusDismissButton: getOptionalElement(root, "[data-coupon-status-dismiss]"),

    summaryPanel: getRequiredElement(root, ".emms__checkout__summary-panel", "summary panel"),
    summaryAccess: getRequiredElement(root, ".emms__checkout__summary-access", "summary access"),
    summaryPricing: getRequiredElement(root, ".emms__checkout__summary", "summary pricing"),
    summaryVipNotice: getRequiredElement(root, "#summary-vip-notice", "summary vip notice"),
    summarySecondaryAction: getRequiredElement(root, "#summary-secondary-action", "summary secondary action"),
    summaryTicket: getRequiredElement(root, "#summary-ticket", "summary ticket"),
    summaryAmount: getRequiredElement(root, "#summary-amount", "summary amount"),
    summaryDiscountRow: getRequiredElement(root, "#summary-discount-row", "summary discount row"),
    summaryDiscount: getRequiredElement(root, "#summary-discount", "summary discount"),
    summaryTotal: getRequiredElement(root, "#summary-total", "summary total"),
    secureNote: getRequiredElement(root, ".emms__checkout__secure-note", "secure note"),
    checkoutStatus: getRequiredElement(root, "#checkout-status", "checkout status"),
    submitButton: getRequiredElement(root, "#submit-payment", "submit button"),

    eprotectContainer: getRequiredElement(root, "#eprotect-container", "eProtect container"),
    eprotectFrame: getRequiredElement(root, "[data-eprotect-frame]", "eProtect frame"),
    eprotectLoading: getRequiredElement(root, "#eprotect-loading", "eProtect loading"),
    eprotectPayframe: getRequiredElement(root, "#eprotect-payframe", "eProtect payframe"),
    paymentMethodStatus: getRequiredElement(root, "#payment-method-status", "payment method status"),

    stepCards: getAllElements(root, "[data-checkout-step-card]"),
    stepperItems: getAllElements(root, "[data-checkout-stepper-item]"),
    stepSummaries: getAllElements(root, "[data-step-summary]"),
    stepEditButtons: getAllElements(root, "[data-step-edit]"),
  };
}

export function clearValidationFeedback(view) {
  [view.customerEmailStatus, view.customerNameStatus, view.customerPhoneStatus, view.customerPoliciesStatus, view.ticketStatus, view.paymentMethodStatus, view.checkoutStatus].forEach((element) =>
    setStatusMessage(element, ""),
  );

  setCouponStatusMessage(view.couponStatus, "");

  [view.emailInput, view.nameInput, view.phoneInput, view.policiesCheckbox].forEach((element) => setFieldInvalidState(element, false));
}

export function setSpinnerVisible(view, show) {
  if (view.spinner) {
    view.spinner.classList.toggle("visible", show);
  }
}

export function showValidationErrors(errors) {
  const renderedTargets = new Set();

  errors.forEach((error) => {
    if (error.element) {
      setFieldInvalidState(error.element, true);
    }

    if (error.target && !renderedTargets.has(error.target)) {
      setStatusMessage(error.target, error.message, true);
      renderedTargets.add(error.target);
    }
  });
}

export function focusFirstInvalidField(errors) {
  const first = errors.find((error) => error.element && typeof error.element.focus === "function");
  if (!first) {
    return;
  }

  first.element.focus({ preventScroll: true });
  if (typeof first.element.scrollIntoView === "function") {
    first.element.scrollIntoView({ behavior: "smooth", block: "center" });
  }
}
