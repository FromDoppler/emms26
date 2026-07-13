import { renderErrorState } from "./errorStateComponent.js";

function byId(id) {
  return document.getElementById(id);
}

export function createCheckoutSuccessComponents() {
  const spinner = byId("spinner");
  const container = byId("checkout-container");

  function setContainerVisible(visible) {
    if (container) {
      container.classList.toggle("emms__checkout__container--hidden", !visible);
    }
  }

  return {
    setSpinnerVisible(visible) {
      if (spinner) {
        spinner.classList.toggle("visible", visible);
      }
    },

    setContainerVisible,

    showErrorState(message, title) {
      renderErrorState(message, title);
      setContainerVisible(true);
    },
  };
}
