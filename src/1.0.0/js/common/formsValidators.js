"use strict";

import { isPhoneValid } from "../intell-input/intell-input.js";

const EMAIL_DOMAIN_SUGGESTION_CLASS = "email-domain-suggestion";

const clearEmailValidationState = (inputEmail) => {
  const parent = inputEmail?.closest(".holder");
  if (!parent) return;

  parent.classList.remove("error", "email-domain-warning");
  parent.removeAttribute("data-error");
  parent.querySelector(`.${EMAIL_DOMAIN_SUGGESTION_CLASS}`)?.remove();
};

const setErrorField = (elem, typeMsg) => {
  const objMessages = {
    required_es: "¡Ouch! El campo está vacío.",
    short_es: "¡Ouch! Escribe al menos dos caracteres.",
    email_es: "¡Ouch! Ingresa un Email válido.",
    URL_es: "¡Ouch! Ingresa una URL válida.",
    number_es: "¡Ouch! Ingresa un número válido.",
    phone_es: "¡Ouch! Ingresa un número de WhatsApp válido.",
    policy_es: "¡Ouch! No has aceptado la Política de Privacidad.",
    dontExist_es: "Ouch, parece que no te has registrado con ese correo… Asegúrate de que esté bien redactado.",
  };

  const parent = elem.closest(".holder");
  if (!parent) return;
  parent.classList.add("error");
  parent.setAttribute("data-error", objMessages[typeMsg]);
};

const setEmailValidationError = (inputEmail, suggestion = null) => {
  const parent = inputEmail?.closest(".holder");
  if (!parent) return;

  clearEmailValidationState(inputEmail);

  if (!suggestion) {
    parent.classList.add("error");
    parent.setAttribute(
      "data-error",
      "¡Ouch! El dominio del Email no parece válido.",
    );
    return;
  }

  const currentDomain = String(inputEmail.value || "").split("@").pop();
  const suggestedDomain = String(suggestion).split("@").pop();

  if (!currentDomain || !suggestedDomain) {
    parent.classList.add("error");
    parent.setAttribute(
      "data-error",
      `¡Ouch! ¿Quisiste escribir ${suggestion}?`,
    );
    return;
  }

  const suggestionBox = document.createElement("div");
  suggestionBox.className = EMAIL_DOMAIN_SUGGESTION_CLASS;
  suggestionBox.setAttribute("role", "alert");

  const comparison = document.createElement("div");
  comparison.className = "email-domain-suggestion__comparison";

  const currentDomainElement = document.createElement("span");
  currentDomainElement.className = "email-domain-suggestion__invalid";
  currentDomainElement.textContent = currentDomain;

  const arrow = document.createElement("span");
  arrow.className = "email-domain-suggestion__arrow";
  arrow.setAttribute("aria-hidden", "true");
  arrow.textContent = "→";

  const suggestedDomainElement = document.createElement("strong");
  suggestedDomainElement.className = "email-domain-suggestion__correct";
  suggestedDomainElement.textContent = suggestedDomain;

  const correctionButton = document.createElement("button");
  correctionButton.type = "button";
  correctionButton.className = "email-domain-suggestion__button";
  correctionButton.textContent = "Corregir";
  correctionButton.setAttribute("aria-label", `Corregir email a ${suggestion}`);
  correctionButton.addEventListener("click", () => {
    inputEmail.value = suggestion;
    clearEmailValidationState(inputEmail);
    inputEmail.dispatchEvent(new Event("input", { bubbles: true }));
    inputEmail.dispatchEvent(new Event("change", { bubbles: true }));
  });

  comparison.append(currentDomainElement, arrow, suggestedDomainElement);
  suggestionBox.append(comparison, correctionButton);
  parent.classList.add("email-domain-warning");
  parent.appendChild(suggestionBox);
};

const validatePolicyCheckbox = (policyCheckbox) => {
  if (!policyCheckbox || policyCheckbox.checked) return true;
  setErrorField(policyCheckbox, "policy_es");
  return false;
};

const validateEmailField = (inputEmail) => {
  if (inputEmail.value) {
    const email = inputEmail.value;
    const emailRegex = new RegExp(
      /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,
    );

    if (email.match(emailRegex)) return true;
    setErrorField(inputEmail, "email_es");
    return false;
  }
};

const validateLengthStrings = (inputsString) => {
  let valid = true;
  inputsString.forEach((input) => {
    if (input.value.length < 2) {
      setErrorField(input, "short_es");
      valid = false;
    }
  });
  return valid;
};

const validateEmptyField = (requiredField) => {
  if (!requiredField.value) {
    setErrorField(requiredField, "required_es");
    return false;
  }
  return true;
};

const resetErrorField = (e) => {
  clearEmailValidationState(e.target);
};

const activeFieldEventsValidator = (form) => {
  form.querySelectorAll("input.required,select.required,input[name='phone']").forEach((elem) => {
    elem.addEventListener("change", resetErrorField);
    elem.addEventListener("keyup", resetErrorField);
    if (elem.matches('input[name="phone"]')) {
      elem.addEventListener("countrychange", resetErrorField);
    }
  });
};

const validateEmptyFields = (form, requiredFields) => {
  activeFieldEventsValidator(form);
  let valid = true;

  Array.from(requiredFields).forEach((elem) => {
    if (!elem.value) {
      setErrorField(elem, "required_es");
      valid = false;
    }
  });

  return valid;
};

const validatePhoneField = (form) => {
  const phone = form.querySelector('input[name="phone"]');
  if (!phone) return true;
  if (!String(phone.value || "").trim()) return !phone.classList.contains("required") && !phone.required;
  if (isPhoneValid(phone)) return true;
  setErrorField(phone, "phone_es");
  return false;
};

const validateForm = (form) => {
  const requiredFields = form.querySelectorAll("input.required,select.required");
  const nameAndLastname = form.querySelectorAll("input.nameLength");
  const checkboxPolicyField = form.querySelector(".acept-politic");
  const emailField = form.querySelector('input[name="email"]');
  const hasStringLength = validateLengthStrings(nameAndLastname);
  const hasRequiredsValidate = validateEmptyFields(form, requiredFields);
  const hasEmailValidate = validateEmailField(emailField);
  const hasPolicyValidate = validatePolicyCheckbox(checkboxPolicyField);
  const hasPhoneValidate = validatePhoneField(form);

  return hasRequiredsValidate && hasEmailValidate && hasPolicyValidate && hasStringLength && hasPhoneValidate;
};

const validateSimpleForm = (form) => {
  const requiredFields = form.querySelectorAll("input.required,select.required");
  const emailField = form.querySelector('input[name="email"]');
  return Boolean(emailField && validateEmptyFields(form, requiredFields) && validatePhoneField(form));
};

const validateEmailStep = (form) => {
  activeFieldEventsValidator(form);
  const emailInput = form.querySelector('input[name="email"]');
  return validateEmptyField(emailInput) && validateEmailField(emailInput);
};

export { validateEmailField, validateEmptyField, validateEmptyFields, validatePolicyCheckbox, validatePhoneField, resetErrorField, validateForm, validateSimpleForm, validateEmailStep, setEmailValidationError };
