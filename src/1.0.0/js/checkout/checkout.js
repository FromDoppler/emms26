import "./pages/checkout/adapters/phoneInputAdapter.js";
import { initCheckout } from "./pages/checkout/checkoutPage.js";

const root = document.querySelector("[data-checkout]");

if (root) {
  initCheckout(root);
}
