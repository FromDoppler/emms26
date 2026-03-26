/**
 * International Telephone Input - Self-contained module
 * Loads the library and initializes phone inputs automatically
 */

// Cargar la librería intlTelInput dinámicamente
const script = document.createElement("script");
script.src = `/src/${window.APP.VERSION}/js/intell-input/intlTelInput.min.js`;
script.onload = function () {
  initIntlTelInput();
};
document.head.appendChild(script);

function initIntlTelInput() {
  const config = {
    separateDialCode: true,
    utilsScript: `/src/${window.APP.VERSION}/js/intell-input/utils.js`,
    initialCountry: "auto",
    geoIpLookup: (callback) => {
      fetch("https://ipapi.co/json")
        .then((res) => res.json())
        .then((data) => callback(data.country_code))
        .catch(() => callback("AR"));
    },
  };

  const input = document.querySelector("#phone");
  if (input) {
    window.intlTelInput(input, config);
  }

  const input2 = document.querySelector("#phone2");
  if (input2) {
    window.intlTelInput(input2, config);
  }
}

// Auto-inicializar cuando el DOM esté listo
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    // El script ya se cargó arriba
  });
} else {
  // DOM ya está listo, cargar inmediatamente
  if (window.intlTelInput) {
    initIntlTelInput();
  }
}
