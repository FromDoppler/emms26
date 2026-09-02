const VERSION = window.APP?.VERSION || "1.0.0";
const ASSET_BASE = `/src/${VERSION}/vendor/intl-tel-input/29.1.1`;
const controls = new WeakMap();
let assetsPromise = null;

const hasRepeatedDigits = (value) => {
  const digits = String(value || "").replace(/\D/g, "");
  return /^(\d)\1{7,}$/.test(digits);
};

const ensureAssets = () => {
  if (typeof window.intlTelInput === "function") return Promise.resolve();
  if (assetsPromise) return assetsPromise;

  if (!document.querySelector('link[data-emms-phone-input="true"]')) {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = `${ASSET_BASE}/css/intlTelInput.min.css`;
    link.dataset.emmsPhoneInput = "true";
    document.head.appendChild(link);
  }

  assetsPromise = new Promise((resolve, reject) => {
    const existing = document.querySelector('script[data-emms-phone-input="true"]');
    if (existing) {
      existing.addEventListener("load", resolve, { once: true });
      existing.addEventListener("error", reject, { once: true });
      return;
    }

    const script = document.createElement("script");
    script.src = `${ASSET_BASE}/js/intlTelInputWithUtils.min.js`;
    script.dataset.emmsPhoneInput = "true";
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });

  return assetsPromise;
};

const createControl = (input) => {
  if (!input || controls.has(input) || typeof window.intlTelInput !== "function") {
    return controls.get(input) || null;
  }

  const language = String(window.APP?.LOCALE || document.documentElement.lang || "es").toLowerCase();
  const instance = window.intlTelInput(input, {
    initialCountry: language.startsWith("en") ? "us" : "ar",
    countryOrder: ["ar", "br", "cl", "mx", "es", "co", "pe", "ec", "us"],
    countryNameLocale: language.startsWith("en") ? "en" : "es",
    separateDialCode: true,
    placeholderNumberPolicy: "AGGRESSIVE",
    placeholderNumberType: "MOBILE",
    strictMode: true,
  });

  const control = {
    getNumber() {
      return String(instance.getNumber?.() || input.value || "").trim();
    },
    isValid() {
      const number = this.getNumber();
      if (!number || hasRepeatedDigits(number)) return false;
      if (typeof instance.isValidNumberPrecise === "function") return Boolean(instance.isValidNumberPrecise());
      if (typeof instance.isValidNumber === "function") return Boolean(instance.isValidNumber());
      return false;
    },
    setNumber(value) {
      const normalized = String(value || "").trim();
      if (typeof instance.setNumber === "function") instance.setNumber(normalized);
      else input.value = normalized;
    },
  };

  controls.set(input, control);
  return control;
};

export const initPhoneInputs = async (root = document) => {
  const inputs = Array.from(root.querySelectorAll("input.phone-number, input[name='phone']"));
  if (!inputs.length) return;

  try {
    await ensureAssets();
    inputs.forEach(createControl);
  } catch (error) {
    console.warn("No se pudo inicializar intl-tel-input", error);
  }
};

export const getPhoneControl = (input) => controls.get(input) || createControl(input);

export const getPhoneNumber = (input) => {
  if (!input || !String(input.value || "").trim()) return null;
  return getPhoneControl(input)?.getNumber() || String(input.value).trim();
};

export const isPhoneValid = (input) => {
  if (!input) return true;
  const hasValue = Boolean(String(input.value || "").trim());
  if (!hasValue) return !input.classList.contains("required") && !input.required;
  return Boolean(getPhoneControl(input)?.isValid());
};

export const setPhoneNumber = (input, value) => {
  if (!input) return;
  const control = getPhoneControl(input);
  if (control) control.setNumber(value);
  else input.value = value || "";
};

const initialize = () => initPhoneInputs(document);
if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initialize, { once: true });
else initialize();
