let eprotectScriptPromise = null;

export function loadEprotectScript() {
  if (window.EprotectIframeClient) {
    return Promise.resolve();
  }

  if (eprotectScriptPromise) {
    return eprotectScriptPromise;
  }

  eprotectScriptPromise = new Promise((resolve, reject) => {
    const config = window.APP?.PAYMENTS?.EPROTECT;
    if (!config || !config.scriptUrl) {
      reject(new Error("EPROTECT_SCRIPT_URL no configurado."));
      return;
    }

    const script = document.createElement("script");
    script.src = config.scriptUrl;
    script.onload = resolve;
    script.onerror = () => {
      eprotectScriptPromise = null;
      reject(new Error("No se pudo cargar eProtect."));
    };
    document.head.appendChild(script);
  });

  return eprotectScriptPromise;
}

export function getMissingEprotectFields() {
  const config = window.APP?.PAYMENTS?.EPROTECT || {};
  return [
    ["scriptUrl", config.scriptUrl],
    ["paypageId", config.paypageId],
    ["reportGroup", config.reportGroup],
  ]
    .filter(([, value]) => !String(value || "").trim())
    .map(([field]) => field);
}

export function resolveEprotectStyle(config) {
  const configuredStyle = String(config?.style || "").trim();
  if (configuredStyle) {
    return configuredStyle;
  }

  const language = String(document.documentElement.lang || navigator.language || "es").toLowerCase();
  return language.startsWith("en") ? "enhancedstyleDB5ENGL" : "enhancedstyleDB5ESPA";
}

export function getEprotectLabels() {
  return {
    accountNumber: "Número de tarjeta",
    expDate: "Vencimiento",
    cvv: "Código de seguridad",
  };
}

export function getEprotectPlaceholderText() {
  return {
    accountNumber: "Número de tarjeta",
    cvv: "CVV",
  };
}

export function getEprotectCustomErrorMessages() {
  return {
    871: "Número de tarjeta inválido.",
    872: "Número de tarjeta inválido.",
    873: "Número de tarjeta inválido.",
    874: "Número de tarjeta inválido.",
    875: "Número de tarjeta inválido.",
    876: "Número de tarjeta inválido.",
    877: "No se pudo validar la tarjeta.",
    878: "No se pudo validar la tarjeta.",
    879: "No se pudo validar la tarjeta.",
    880: "No se pudo validar la tarjeta.",
    881: "Código de seguridad inválido.",
    882: "Código de seguridad inválido.",
    883: "Código de seguridad inválido.",
    884: "No se pudo validar la tarjeta.",
    885: "No se pudo validar la tarjeta.",
    886: "Fecha de vencimiento inválida.",
    "886-month": "Fecha de vencimiento inválida.",
    "886-year": "Fecha de vencimiento inválida.",
    887: "No se pudo validar la tarjeta.",
    888: "No se pudo validar la tarjeta.",
    889: "No se pudo validar la tarjeta.",
    893: "No se pudo validar la tarjeta.",
    894: "No se pudo validar la tarjeta.",
    898: "No se pudo validar la tarjeta.",
  };
}

export function mapWorldpayCardType(type) {
  const value = String(type || "")
    .trim()
    .toUpperCase();

  if (value === "VI" || value === "VISA") {
    return 1;
  }

  if (value === "MC" || value === "MASTERCARD") {
    return 2;
  }

  if (value === "AX" || value === "AMEX" || value === "AMERICAN_EXPRESS") {
    return 3;
  }

  if (value === "DI" || value === "DISCOVER") {
    return 4;
  }

  const numericType = Number.parseInt(value, 10);
  return Number.isNaN(numericType) ? null : numericType;
}

export function resolveEprotectErrorMessage(response) {
  const code = String(response?.response || "");
  const mapped = {
    871: "Revisá el número de tarjeta.",
    872: "Revisá el número de tarjeta.",
    873: "Revisá el número de tarjeta.",
    874: "Revisá el número de tarjeta.",
    875: "Revisá el número de tarjeta.",
    876: "Revisá el número de tarjeta.",
    881: "Revisá el código de seguridad.",
    882: "Revisá el código de seguridad.",
    883: "Revisá el código de seguridad.",
    886: "Revisá la fecha de vencimiento.",
    "886-month": "Revisá la fecha de vencimiento.",
    "886-year": "Revisá la fecha de vencimiento.",
  };

  return mapped[code] || "Revisá los datos de la tarjeta.";
}
