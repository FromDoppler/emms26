"use strict";

import { customError } from "./common/customsError.js";
import { submitFormFetch, submitModalForm, submitWithoutForm } from "./common/submitForm.js";
import { validateForm } from "./common/formsValidators.js";
import { alreadyAccountListener, swichFormListener } from "./common/switchForm.js";
import { closeModal, openModal } from "../../../components/modal/openModal.js";

const redirectToRegisteredPage = () => {
  const currentPath = window.location.pathname.replace(/^\//, "");

  // Special case for sponsors (preserve slug)
  if (currentPath === "sponsors") {
    const slug = sessionStorage.getItem("currentSlug");
    const baseUrl = window.APP.EVENTS.CURRENT.sharedPages.sponsors.registered.url;
    window.location.href = slug && slug !== "null" ? `/${baseUrl}?slug=${slug}` : `/${baseUrl}`;
  } else {
    // Default: go to event's registered page
    window.location.href = window.APP.utils.addParams(`/${window.APP.EVENTS.CURRENT.pages.registered.url}`);
  }
};

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
    const submitBtn = form.querySelector("button");
    if (submitBtn) submitBtn.addEventListener("click", (e) => submitFormHandler(e, form));
    swichFormListener(form); // usando nombre original con typo
  }

  if (modalForm) {
    const submitBtn = modalForm.querySelector("button");
    if (submitBtn) submitBtn.addEventListener("click", (e) => submitFormHandler(e, modalForm));
  }

  if (extraData) {
    modalForm.addEventListener("submit", modalFormSubmitHandler);
  }

  alreadyRegisterButtons.forEach((btn) => btn.addEventListener("click", () => quickSubmitHandler(btn)));
  if (alreadyAccountForm) {
    alreadyAccountListener();
  }
};

document.addEventListener("DOMContentLoaded", initializeEventListeners);
