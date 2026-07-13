(function () {
  const DEFAULT_COUNTRY = String(document.documentElement.lang || "")
    .toLowerCase()
    .startsWith("en")
    ? "us"
    : "ar";
  const CHECKOUT_ROOT = document.querySelector("[data-checkout]");
  const CHECKOUT_PHONE_COUNTRY = normalizeCountryCode(CHECKOUT_ROOT && CHECKOUT_ROOT.dataset ? CHECKOUT_ROOT.dataset.phoneCountry : "");
  const INITIAL_COUNTRY = CHECKOUT_PHONE_COUNTRY || DEFAULT_COUNTRY;
  const COUNTRY_ORDER = buildCountryOrder(INITIAL_COUNTRY);

  function normalizeCountryCode(value) {
    const countryCode = String(value || "")
      .trim()
      .toLowerCase();
    return /^[a-z]{2}$/.test(countryCode) ? countryCode : "";
  }

  function buildCountryOrder(primaryCountry) {
    const baseOrder = [primaryCountry, "ar", "br", "cl", "mx", "es", "co", "pe", "ec", "us"];
    const seen = new Set();
    return baseOrder.filter(function (countryCode) {
      const normalized = normalizeCountryCode(countryCode);
      if (!normalized || seen.has(normalized)) {
        return false;
      }
      seen.add(normalized);
      return true;
    });
  }

  function hasRepeatedDigits(value) {
    const digits = String(value || "").replace(/\D/g, "");
    return /^(\d)\1{7,}$/.test(digits);
  }

  function buildFallbackControl(input, error, options) {
    if (typeof options.onError === "function") {
      options.onError(error);
    }

    return {
      isReady: function () {
        return false;
      },
      hasFailed: function () {
        return true;
      },
      getNumber: function () {
        return String(input && input.value ? input.value : "").trim();
      },
      isValid: function () {
        return false;
      },
      setNumber: function (value) {
        if (input) {
          input.value = value || "";
        }
      },
      destroy: function () {},
    };
  }

  function init(input, options = {}) {
    if (!input) {
      return buildFallbackControl(null, new Error("No se encontró el input de teléfono."), options);
    }

    if (typeof window.intlTelInput !== "function") {
      return buildFallbackControl(input, new Error("intl-tel-input no está disponible."), options);
    }

    const instance = window.intlTelInput(input, {
      initialCountry: INITIAL_COUNTRY,
      countryOrder: COUNTRY_ORDER,
      matchDropdownWidth: true,
      dropdownContainer: document.body,
      showFlags: true,
      separateDialCode: true,
      placeholderNumberPolicy: "AGGRESSIVE",
      placeholderNumberType: "MOBILE",
      customPlaceholder: function (exampleNumber) {
        return exampleNumber || "Teléfono";
      },
      strictMode: true,
    });

    if (typeof instance.setSelectedCountry === "function") {
      try {
        instance.setSelectedCountry(INITIAL_COUNTRY);
      } catch (error) {
        // Keep the widget usable even if the country selector rejects the country code.
      }
    }

    let suppressProgrammaticChange = false;

    const notifyChange = function () {
      if (suppressProgrammaticChange) {
        return;
      }

      const hasValue = Boolean(String(input.value || "").trim());
      input.setAttribute("aria-invalid", hasValue && !control.isValid() ? "true" : "false");

      if (typeof options.onChange === "function") {
        options.onChange(control);
      }
    };

    const control = {
      isReady: function () {
        return true;
      },
      hasFailed: function () {
        return false;
      },
      getNumber: function () {
        if (typeof instance.getNumber === "function") {
          return String(instance.getNumber() || "").trim();
        }

        return String(input.value || "").trim();
      },
      isValid: function () {
        const phoneNumber = control.getNumber();
        if (!phoneNumber) {
          return false;
        }

        if (hasRepeatedDigits(phoneNumber)) {
          return false;
        }

        if (typeof instance.isValidNumberPrecise === "function") {
          return Boolean(instance.isValidNumberPrecise()) && !hasRepeatedDigits(phoneNumber);
        }

        if (typeof instance.isValidNumber === "function") {
          return Boolean(instance.isValidNumber()) && !hasRepeatedDigits(phoneNumber);
        }

        return false;
      },
      setNumber: function (value) {
        const normalizedValue = String(value || "").trim();
        const currentValue = String(input.value || "").trim();
        const currentNumber = control.getNumber();

        if (currentValue === normalizedValue || currentNumber === normalizedValue) {
          input.setAttribute("aria-invalid", normalizedValue && !control.isValid() ? "true" : "false");
          return;
        }

        suppressProgrammaticChange = true;

        try {
          if (typeof instance.setNumber === "function") {
            instance.setNumber(normalizedValue);
          } else {
            input.value = normalizedValue;
          }

          input.setAttribute("aria-invalid", normalizedValue && !control.isValid() ? "true" : "false");
        } finally {
          window.setTimeout(function () {
            suppressProgrammaticChange = false;
          }, 0);
        }
      },
      destroy: function () {
        input.removeEventListener("input", notifyChange);
        input.removeEventListener("countrychange", notifyChange);

        if (typeof instance.destroy === "function") {
          instance.destroy();
        }
      },
    };

    input.addEventListener("input", notifyChange);
    input.addEventListener("countrychange", notifyChange);

    input.setAttribute("aria-invalid", "false");

    if (typeof options.onReady === "function") {
      options.onReady(control);
    }

    return control;
  }

  window.CheckoutPhoneInput = {
    init: init,
  };
})();
