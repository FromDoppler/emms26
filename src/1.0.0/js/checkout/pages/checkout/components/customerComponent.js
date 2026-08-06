import { buildCustomerData } from "../state/checkoutState.js";
import { clearValidationFeedback, setFieldInvalidState, setStatusMessage, setElementText } from "./checkoutComponents.js";
import { getCustomerDisplayPhone, isEditableCustomerMode, isValidEmail, normalizeEmail, shouldAskConsents, STEPS } from "../state/checkoutSelectors.js";
import { getCustomerPayload } from "../checkoutPayloads.js";
import { readCheckoutSnapshot } from "../checkoutSnapshot.js";
import { isPhoneInputEditable, validateCustomerStep as validateCustomerStepRules } from "../checkoutRules.js";
import { resolveCustomerMode } from "./customerRules.js";

function syncInputValue(input, value) {
  if (!input || document.activeElement === input) {
    return;
  }

  const nextValue = String(value || "");
  if (input.value !== nextValue) {
    input.value = nextValue;
  }
}

function readRenderedPhoneNumber(state, view) {
  if (state.phoneControl && typeof state.phoneControl.getNumber === "function") {
    return String(state.phoneControl.getNumber() || "").trim();
  }

  return String(view.phoneInput?.value || "").trim();
}

function setRenderedPhoneNumber(state, view, value) {
  const normalizedValue = String(value || "").trim();

  if (state.phoneControl && typeof state.phoneControl.setNumber === "function") {
    state.phoneControl.setNumber(normalizedValue);
    return;
  }

  if (view.phoneInput) {
    view.phoneInput.value = normalizedValue;
  }
}

function getCustomerValidationTargets(view) {
  return {
    customerEmailStatus: view.customerEmailStatus,
    customerNameStatus: view.customerNameStatus,
    customerPhoneStatus: view.customerPhoneStatus,
    customerPoliciesStatus: view.customerPoliciesStatus,
    emailInput: view.emailInput,
    nameInput: view.nameInput,
    phoneInput: view.phoneInput,
    policiesCheckbox: view.policiesCheckbox,
  };
}

function setSummaryVariant(view, mode) {
  view.profileSummary.classList.remove("emms__checkout__customer-summary--warning", "emms__checkout__customer-summary--error", "emms__checkout__customer-summary--vip");

  if (mode === "recognized_incomplete") {
    view.profileSummary.classList.add("emms__checkout__customer-summary--warning");
  }

  if (mode === "vip") {
    view.profileSummary.classList.add("emms__checkout__customer-summary--vip");
  }
}

export function renderCustomerView(state, view) {
  const mode = state.customerMode;
  const isEmailMode = mode === "email";
  const isEditableMode = isEditableCustomerMode(state);
  const isSummaryMode = mode === "recognized_complete" || mode === "recognized_incomplete" || mode === "vip";
  const consentsVisible = isEditableMode && shouldAskConsents(state.customerProfile);
  const isPaymentLocked = Boolean(state.paymentInFlight || state.activePaymentId);

  view.customerCard.dataset.customerMode = mode;
  view.emailStep.hidden = !isEmailMode;
  view.emailInput.readOnly = !isEmailMode || isPaymentLocked;

  view.customerFields.hidden = !isEditableMode;
  view.profileSummary.hidden = !isSummaryMode;
  view.consentsWrapper.hidden = !consentsVisible;
  view.customerStepActions.hidden = state.currentStep !== STEPS.CUSTOMER_DATA || mode === "email" || mode === "vip";

  syncInputValue(view.emailInput, state.customerData.email);
  syncInputValue(view.nameInput, state.customerData.name);
  const nextPhoneValue = state.customerData.phone || "";
  const currentPhoneValue = readRenderedPhoneNumber(state, view);
  if (document.activeElement !== view.phoneInput && currentPhoneValue !== nextPhoneValue) {
    setRenderedPhoneNumber(state, view, nextPhoneValue);
  }

  view.nameInput.readOnly = (mode === "recognized_incomplete" && Boolean(state.customerData.name)) || isPaymentLocked;
  view.phoneInput.readOnly = (mode === "recognized_incomplete" && Boolean(state.customerData.phone)) || isPaymentLocked;
  view.policiesCheckbox.disabled = Boolean(isPaymentLocked);
  view.promotionsCheckbox.disabled = Boolean(isPaymentLocked);
  view.resolveCustomerButton.disabled = Boolean(isPaymentLocked);
  view.customerNextStepButton.disabled = Boolean(isPaymentLocked);

  setSummaryVariant(view, mode);
  setElementText(view.customerSummaryTitle, mode === "vip" ? "Ya tenés acceso VIP" : "Encontramos tu registro");

  if (mode === "recognized_incomplete") {
    setElementText(view.customerSummaryCopy, "Completá los datos o permisos faltantes para continuar.");
  } else if (mode === "vip") {
    setElementText(view.customerSummaryCopy, "Este correo ya cuenta con acceso VIP para este evento.");
  } else {
    setElementText(view.customerSummaryCopy, "Estos son los datos asociados a tu correo.");
  }

  setElementText(view.customerSummaryName, state.customerData.name || "Datos pendientes");
  setElementText(view.customerSummaryPhone, getCustomerDisplayPhone(state));

  if (!consentsVisible) {
    view.policiesCheckbox.checked = false;
    view.promotionsCheckbox.checked = false;
    setFieldInvalidState(view.policiesCheckbox, false);
    setStatusMessage(view.customerPoliciesStatus, "");
  }

  if (isPaymentLocked) {
    setStatusMessage(view.customerPoliciesStatus, "");
    setFieldInvalidState(view.policiesCheckbox, false);
  }
}

export function clearCustomerValidation(view) {
  [view.customerEmailStatus, view.customerNameStatus, view.customerPhoneStatus, view.customerPoliciesStatus].forEach((element) => {
    setStatusMessage(element, "");
  });

  [view.emailInput, view.nameInput, view.phoneInput, view.policiesCheckbox].forEach((element) => {
    setFieldInvalidState(element, false);
  });
}

export function createCustomerComponent(context) {
  const { store, view, render } = context;

  function syncEditableCustomerFields() {
    const state = store.getState();

    if (state.paymentInFlight || state.activePaymentId) {
      return;
    }

    const snapshot = readCheckoutSnapshot(view, state);
    if (state.customerMode !== "anonymous_editing" && state.customerMode !== "recognized_incomplete") {
      return;
    }

    const patch = {};

    if (!view.nameInput.readOnly) {
      patch.name = snapshot.name;
    }

    if (isPhoneInputEditable(state, snapshot)) {
      patch.phone = snapshot.phone;
    }

    if (Object.keys(patch).length > 0) {
      store.dispatch({
        type: "CUSTOMER_DATA_CHANGED",
        customerData: patch,
      });
    }
  }

  function collectCustomerPayload(snapshot = readCheckoutSnapshot(view, store.getState())) {
    syncEditableCustomerFields();
    return getCustomerPayload(store.getState(), snapshot);
  }

  function validateCustomerStep(snapshot = readCheckoutSnapshot(view, store.getState())) {
    return validateCustomerStepRules(store.getState(), snapshot, getCustomerValidationTargets(view));
  }

  function resolveCustomerProfile(customerProfile) {
    const state = store.getState();
    const snapshot = readCheckoutSnapshot(view, state);
    const email = normalizeEmail(snapshot.email || state.customerData.email);
    const currentName = snapshot.name || state.customerData.name || "";
    const currentPhone = snapshot.phone || state.customerData.phone || "";

    if (!email) {
      return null;
    }

    if (customerProfile && customerProfile.exists) {
      const profileName = String(customerProfile.firstname || "").trim();
      const profilePhone = String(customerProfile.phone || "").trim();
      const customerData = buildCustomerData({
        email,
        name: profileName || currentName || "",
        phone: profilePhone || currentPhone || "",
        profileExists: true,
        isFree: Boolean(customerProfile.isFree),
        isVip: Boolean(customerProfile.isVip),
      });

      return {
        profile: customerProfile,
        customerData,
        email,
        mode: resolveCustomerMode(customerProfile, customerData),
      };
    }

    return {
      profile: null,
      customerData: buildCustomerData({
        email,
        name: currentName,
        phone: currentPhone,
      }),
      email,
      mode: "anonymous_editing",
    };
  }

  function resolveCustomerEmail() {
    const email = normalizeEmail(view.emailInput.value);

    if (!isValidEmail(email)) {
      setFieldInvalidState(view.emailInput, true);
      setStatusMessage(view.customerEmailStatus, "Ingresá un email válido.", true);
      view.emailInput.focus();
      return false;
    }

    setFieldInvalidState(view.emailInput, false);
    setStatusMessage(view.customerEmailStatus, "");
    store.dispatch({ type: "EMAIL_ACCEPTED", email });
    return true;
  }

  function handleEmailInput() {
    if (store.getState().customerMode !== "email") {
      return false;
    }

    setStatusMessage(view.customerEmailStatus, "");
    setFieldInvalidState(view.emailInput, false);
    setStatusMessage(view.checkoutStatus, "");
    return true;
  }

  function handleProfileInput(element) {
    syncEditableCustomerFields();
    setStatusMessage(view.checkoutStatus, "");

    if (element === view.nameInput) {
      setStatusMessage(view.customerNameStatus, "");
      setFieldInvalidState(view.nameInput, false);
    }

    if (element === view.phoneInput) {
      setStatusMessage(view.customerPhoneStatus, "");
      setFieldInvalidState(view.phoneInput, false);
    }

    return true;
  }

  function handleConsentChange() {
    setStatusMessage(view.customerPoliciesStatus, "");
    setFieldInvalidState(view.policiesCheckbox, false);
    setStatusMessage(view.checkoutStatus, "");
    return true;
  }

  function startIdentificationEditing() {
    store.dispatch({ type: "IDENTIFICATION_EDITING_STARTED" });
    clearValidationFeedback(view);
  }

  function startCustomerEditing() {
    clearCustomerValidation(view);
    store.dispatch({ type: "CUSTOMER_EDITING_STARTED" });
  }

  function initializePhoneInput(onPhoneInputChanged = function noop() {}) {
    if (!view.phoneInput || !window.CheckoutPhoneInput || typeof window.CheckoutPhoneInput.init !== "function") {
      store.dispatch({ type: "PHONE_FAILED" });
      setStatusMessage(view.customerPhoneStatus, "No se pudo cargar el selector de teléfono.", true);
      return;
    }

    const control = window.CheckoutPhoneInput.init(view.phoneInput, {
      onReady: function (readyControl) {
        store.dispatch({
          type: "PHONE_READY",
          control: readyControl,
        });
        syncEditableCustomerFields();
        onPhoneInputChanged();
      },
      onChange: function () {
        syncEditableCustomerFields();
        setStatusMessage(view.checkoutStatus, "");
        onPhoneInputChanged();
      },
      onError: function (error) {
        store.dispatch({ type: "PHONE_FAILED" });
        setStatusMessage(view.customerPhoneStatus, (error && error.message) || "No se pudo cargar el selector de teléfono.", true);
        render();
      },
    });

    if (control && typeof control.isReady === "function" && control.isReady() && !store.getState().phoneControl) {
      store.dispatch({
        type: "PHONE_READY",
        control,
      });
    }
  }

  return {
    render(state = store.getState()) {
      renderCustomerView(state, view);
    },
    clearValidation() {
      clearCustomerValidation(view);
    },
    collectCustomerPayload,
    handleConsentChange,
    handleEmailInput,
    handleProfileInput,
    initializePhoneInput,
    resolveCustomerEmail,
    resolveCustomerProfile,
    startCustomerEditing,
    startIdentificationEditing,
    syncEditableCustomerFields,
    validateCustomerStep,
  };
}
