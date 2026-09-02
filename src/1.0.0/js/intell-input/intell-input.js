const VERSION = window.APP?.VERSION || "1.0.0";
const ASSET_BASE = `/src/${VERSION}/vendor/intl-tel-input/29.1.1`;
const COUNTRY_ENDPOINT = "/services/getCountryNameAndCode.php";
const PREFERRED_COUNTRIES = ["ar", "br", "cl", "mx", "es", "co", "pe", "ec", "us"];
const controls = new WeakMap();
let assetsPromise = null;
let countryPromise = null;

const hasRepeatedDigits = (value) => {
  const digits = String(value || "").replace(/\D/g, "");
  return /^(\d)\1{7,}$/.test(digits);
};

const getLanguage = () => {
  const language = window.APP?.LOCALE || document.documentElement.lang || "es";
  return String(language).trim().toLowerCase();
};

const getFallbackCountry = () => {
  return getLanguage().startsWith("en") ? "us" : "ar";
};

const normalizeCountryCode = (value) => {
  const countryCode = String(value || "").trim().toLowerCase();
  if (!/^[a-z]{2}$/.test(countryCode) || countryCode === "xx") return "";
  return countryCode;
};

const fetchInitialCountry = async () => {
  try {
    const response = await fetch(COUNTRY_ENDPOINT, { headers: { Accept: "application/json" } });
    if (!response.ok) throw new Error(`Country lookup failed: ${response.status}`);

    const data = await response.json();
    return normalizeCountryCode(data?.countryCode) || getFallbackCountry();
  } catch {
    return getFallbackCountry();
  }
};

const resolveInitialCountry = () => {
  if (!countryPromise) countryPromise = fetchInitialCountry();
  return countryPromise;
};

const ensureAssets = () => {
  if (typeof window.intlTelInput === "function") return Promise.resolve();
  if (assetsPromise) return assetsPromise;

  const styleSelector = 'link[data-emms-phone-input="true"]';
  if (!document.querySelector(styleSelector)) {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = `${ASSET_BASE}/css/intlTelInput.min.css`;
    link.dataset.emmsPhoneInput = "true";
    document.head.appendChild(link);
  }

  assetsPromise = new Promise((resolve, reject) => {
    const scriptSelector = 'script[data-emms-phone-input="true"]';
    const existing = document.querySelector(scriptSelector);
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

const createControl = (input, initialCountry) => {
  if (!input || controls.has(input) || typeof window.intlTelInput !== "function") return controls.get(input) || null;

  const language = getLanguage();
  const country = normalizeCountryCode(initialCountry) || getFallbackCountry();
  const countries = [country, ...PREFERRED_COUNTRIES];
  const countryOrder = Array.from(new Set(countries));
  const instance = window.intlTelInput(input, {
    initialCountry: country,
    countryOrder,
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
  const selector = "input.phone-number, input[name='phone']";
  const inputs = Array.from(root.querySelectorAll(selector));
  if (!inputs.length) return;

  try {
    const promises = [ensureAssets(), resolveInitialCountry()];
    const [, initialCountry] = await Promise.all(promises);
    inputs.forEach((input) => createControl(input, initialCountry));
  } catch (error) {
    console.warn("No se pudo inicializar intl-tel-input", error);
  }
};

export const getPhoneControl = (input) => controls.get(input) || null;

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
