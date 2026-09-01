/**
 * Autocompleta los formularios de registro y captación con datos recibidos por URL.
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

  completeForms() {
    const { email, phone, name } = this.getUserValues();
    const forms = document.querySelectorAll("#commonForm, #modalForm");

    if (!forms.length) {
      console.warn("No se encontraron formularios de registro o captación en la página.");
      return;
    }

    forms.forEach((form) => {
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
    });
  },

  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.completeForms();
    });
  },
};

FormAutoComplete.init();
