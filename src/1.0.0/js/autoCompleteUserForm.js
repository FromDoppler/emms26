/**
 * Autocompleta el formulario principal de registro con datos recibidos por URL.
 *
 * Los parámetros soportados son `email`, `name` y `phone`. La URL puede contener
 * caracteres percent-encoded y también entidades HTML heredadas.
 */

function decodeHtmlEntitiesInUrl(url) {
  const textarea = document.createElement("textarea");
  textarea.innerHTML = url;
  return textarea.value;
}

function searchUrlParam(param) {
  const decodedUrl = decodeHtmlEntitiesInUrl(window.location.href);
  const urlParams = new URL(decodedUrl).searchParams;

  return urlParams.get(param);
}

const FormAutoComplete = {
  getUserValues() {
    return {
      email: searchUrlParam("email"),
      name: searchUrlParam("name"),
      phone: searchUrlParam("phone"),
    };
  },

  completeForm() {
    const { email, phone, name } = this.getUserValues();
    const form = document.getElementById("commonForm");

    if (!form) {
      console.warn("No se encontró el formulario principal de registro en la página.");
      return;
    }

    form.querySelectorAll("input").forEach((input) => {
      switch (input.name) {
        case "email":
          input.value = email || "";
          break;
        case "name":
          input.value = name || "";
          break;
        case "phone":
          input.value = phone || "";
          break;
      }
    });
  },

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.completeForm();
    });
  },
};

FormAutoComplete.init();
