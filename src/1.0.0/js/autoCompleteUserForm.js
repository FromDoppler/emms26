import { initPhoneInputs, setPhoneNumber } from "./intell-input/intell-input.js";

function decodeHtmlEntitiesInUrl(url) {
  const textarea = document.createElement("textarea");
  textarea.innerHTML = url;
  return textarea.value;
}

function searchUrlParam(param) {
  const decodedUrl = decodeHtmlEntitiesInUrl(window.location.href);
  return new URL(decodedUrl).searchParams.get(param);
}

const FormAutoComplete = {
  getUserValues() {
    return { email: searchUrlParam("email"), name: searchUrlParam("name"), phone: searchUrlParam("phone") };
  },

  async completeForms() {
    const { email, phone, name } = this.getUserValues();
    const forms = document.querySelectorAll("#commonForm, #modalForm");
    if (!forms.length) return;

    const phoneInitialization = initPhoneInputs(document);

    forms.forEach((form) => {
      form.querySelectorAll("input").forEach((input) => {
        if (input.name === "email") input.value = email || "";
        if (input.name === "name") input.value = name || "";
      });
    });

    await phoneInitialization;

    forms.forEach((form) => {
      const phoneInput = form.querySelector('input[name="phone"]');
      setPhoneNumber(phoneInput, phone || "");
    });
  },

  init() {
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", () => this.completeForms(), { once: true });
    else this.completeForms();
  },
};

FormAutoComplete.init();
