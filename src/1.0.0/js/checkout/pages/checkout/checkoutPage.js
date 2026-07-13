import { createCheckoutFlow } from "./checkoutFlow.js";
import { createCheckoutComponents } from "./components/checkoutComponents.js";
import { checkoutReducer } from "./state/checkoutReducer.js";
import { createCheckoutState } from "./state/checkoutState.js";
import { createCheckoutStore } from "./state/checkoutStore.js";

export function initCheckout(root = document.querySelector("[data-checkout]")) {
  if (!root || root.dataset.checkoutInitialized === "true") {
    return;
  }

  root.dataset.checkoutInitialized = "true";

  const checkoutView = createCheckoutComponents(root);
  const store = createCheckoutStore(createCheckoutState(root), checkoutReducer);
  const flow = createCheckoutFlow({ store, view: checkoutView });

  flow.init();
}
