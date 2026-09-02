"use strict";

import { customError } from "./common/customsError.js";
import { checkRegistrationStatus, submitFormFetch, redirectToRegisteredPage } from "./common/submitForm.js";
import { validateEmailStep } from "./common/formsValidators.js";
import { setEventInLocalStorage } from "./common/submitHelpers.js";
import { toHex } from "./common/decodeEmail.js";

const setButtonLoading = (button, isLoading) => {
  button.classList.toggle("button--loading", isLoading);
  button.disabled = isLoading;
};

const STEP_TRANSITION_MS = 280;

const prefersReducedMotion = () => typeof window !== "undefined" && window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const goToStepTwo = (form, stepOneEl, stepTwoEl) => {
  const nameInput = stepTwoEl.querySelector('input[name="name"]');

  if (prefersReducedMotion()) {
    stepOneEl.hidden = true;
    stepTwoEl.hidden = false;
    form.dataset.step = "2";
    nameInput?.focus();
    return Promise.resolve();
  }

  stepOneEl.classList.add("is-leaving");

  return new Promise((resolve) => {
    window.setTimeout(() => {
      stepOneEl.hidden = true;
      stepOneEl.classList.remove("is-leaving");

      stepTwoEl.classList.add("is-entering");
      stepTwoEl.hidden = false;
      form.dataset.step = "2";
      nameInput?.focus();

      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          stepTwoEl.classList.remove("is-entering");
        });
      });

      resolve();
    }, STEP_TRANSITION_MS);
  });
};

const handleEmailStep = async (form, stepOneEl, stepTwoEl, stepOneButton) => {
  if (stepOneButton.disabled) return;
  if (!validateEmailStep(form)) return;

  setButtonLoading(stepOneButton, true);

  const emailInput = form.querySelector('input[name="email"]');
  const emailValue = emailInput.value;

  try {
    const registered = await checkRegistrationStatus(emailValue);

    if (registered === true) {
      setEventInLocalStorage(window.APP.EVENTS.CURRENT.freeId, toHex(emailValue));
      redirectToRegisteredPage();
      return;
    }

    // false OR null (fail-open): reveal step 2
    await goToStepTwo(form, stepOneEl, stepTwoEl);
  } finally {
    setButtonLoading(stepOneButton, false);
  }
};

const handleSubmitStep = async (form, stepTwoButton) => {
  if (stepTwoButton.disabled) return;
  setButtonLoading(stepTwoButton, true);

  try {
    const result = await submitFormFetch(form, window.APP.EVENTS.CURRENT.freeId);
    if (result === undefined) return;
    if (result === null) {
      throw new Error("Fetch fail in heroRegistrationFlow submit");
    }

    const { fetchResp } = result;
    if (!fetchResp.ok) {
      throw new Error(`Server error: ${fetchResp.status}`);
    }

    redirectToRegisteredPage();
  } catch (error) {
    customError("Error en flujo email-first", error);
  } finally {
    setButtonLoading(stepTwoButton, false);
  }
};

export const initHeroRegistrationFlow = (form) => {
  if (!form) return;

  const stepOneEl = form.querySelector('.emms__form__step[data-step="1"]');
  const stepTwoEl = form.querySelector('.emms__form__step[data-step="2"]');
  if (!stepOneEl || !stepTwoEl) return;

  const stepOneButton = stepOneEl.querySelector("button");
  const stepTwoButton = stepTwoEl.querySelector("button");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    if (form.dataset.step === "1") {
      handleEmailStep(form, stepOneEl, stepTwoEl, stepOneButton);
      return;
    }

    if (form.dataset.step === "2") {
      handleSubmitStep(form, stepTwoButton);
    }
  });
};
