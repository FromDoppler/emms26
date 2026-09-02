"use strict";

import { customError } from "./common/customsError.js";
import { submitFormFetch, submitModalForm, submitWithoutForm, redirectToRegisteredPage } from "./common/submitForm.js";
import { validateForm } from "./common/formsValidators.js";
import { alreadyAccountListener, swichFormListener } from "./common/switchForm.js";
import { closeModal, openModal } from "../../../components/modal/openModal.js";
import { initHeroRegistrationFlow } from "./heroRegistrationFlow.js";

// Form submit handler
const submitFormHandler = async (e, form) => {
  e.preventDefault();
  if (!form) return;

  if (!validateForm(form)) return;

  try {
    const { fetchResp: resp } = await submitFormFetch(form, window.APP.EVENTS.CURRENT.freeId);
    if (!resp.ok) throw new Error(`Server error: ${resp.status}`);
    redirectToRegisteredPage();
  } catch (error) {
    customError("Error en formulario", error);
  }
};

// Button-only submit handler (sin formulario)
const quickSubmitHandler = async (button) => {
  button.classList.add("button--loading");
  button.disabled = true;

  try {
    const { fetchResp: resp } = await submitWithoutForm(window.APP.EVENTS.CURRENT.freeId);
    if (!resp.ok) throw new Error(`Server error: ${resp.status}`);
    redirectToRegisteredPage();
  } catch (error) {
    customError("Error sin formulario", error);
  } finally {
    button.classList.remove("button--loading");
    button.disabled = false;
  }
};

const modalFormSubmitHandler = async (e) => {
  e.preventDefault();
  const modalForm = document.getElementById("formExtraData");
  if (!modalForm) return;

  try {
    const { fetchResp: resp } = await submitModalForm(modalForm, window.APP.EVENTS.CURRENT.freeId, "extraDataModal");
    if (!resp?.ok) {
      throw new Error(`Server error: ${resp?.status}`);
    }
    closeModal("modalForm");
  } catch (error) {
    customError("Error en formulario adicional", error);
  }
};

// Initialization
const initializeEventListeners = () => {
  const form = document.getElementById("commonForm");
  const modalForm = document.getElementById("modalForm");
  const alreadyRegisterButtons = document.querySelectorAll(".alreadyRegisterForm");
  const extraData = document.getElementById("formExtraData");
  const alreadyAccountForm = document.getElementById("alreadyAccountForm");

  if (form) {
    const isEmailFirstFlow = form.dataset.registrationFlow === "email-first";
    if (isEmailFirstFlow) {
      initHeroRegistrationFlow(form);
    } else {
      form.addEventListener("submit", (e) => submitFormHandler(e, form));
      swichFormListener(form); // usando nombre original con typo
    }
  }

  if (modalForm?.tagName === "FORM") {
    modalForm.addEventListener("submit", (e) => submitFormHandler(e, modalForm));
  }

  if (extraData && modalForm) {
    modalForm.addEventListener("submit", modalFormSubmitHandler);
  }

  alreadyRegisterButtons.forEach((btn) => btn.addEventListener("click", () => quickSubmitHandler(btn)));
  if (alreadyAccountForm) {
    alreadyAccountListener();
  }
};

document.addEventListener("DOMContentLoaded", initializeEventListeners);
