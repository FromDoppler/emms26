/**
 * Script diseñado para manejar correctamente URLs que contienen parámetros codificados en HTML.
 *
 * Este script es especialmente útil cuando los parámetros de la URL incluyen caracteres especiales
 * como tildes, ñs, o símbolos, los cuales están codificados como entidades HTML. Ejemplo de URL:
 *
 * http://localhost/digital-trends?email=mfantini@fromdoppler.com&name=Mart&iacute;n%20Fantini&phone=2234456
 *
 * El script se encarga de:
 * 1. Decodificar las entidades HTML en los parámetros de la URL.
 * 2. Extraer y manejar los valores de los parámetros 'email', 'name', y 'phone' si están presentes.
 * 3. Completar un formulario con estos valores, si se encuentran en la URL.
 *
 * El script está preparado para manejar la ausencia de algunos parámetros. Si un parámetro no está presente,
 * simplemente no se asignará ningún valor al campo correspondiente en el formulario.
 */

function decodeHtmlEntitiesInUrl(url) {
  const entityMap = {
    "&iacute;": "í",
    "&eacute;": "é",
    "&oacute;": "ó",
    "&aacute;": "á",
    "&uacute;": "ú",
    "&ntilde;": "ñ",
    "&egrave;": "è",
    "&nbsp;": " ",
  };

  for (let entity in entityMap) {
    const regex = new RegExp(entity, "g");
    url = url.replace(regex, entityMap[entity]);
  }
  return url;
}

function searchUrlParam(param) {
  const url = window.location.href;
  const decodedUrl = decodeHtmlEntitiesInUrl(url);

  const urlParams = new URLSearchParams(decodedUrl.split("?")[1]);
  const value = urlParams.get(param);

  return value ? decodeURIComponent(value.replace(/\+/g, " ")) : null;
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
    const forms = document.querySelectorAll("form");

    if (!forms.length) {
      console.warn("No se encontraron formularios en la página.");
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
