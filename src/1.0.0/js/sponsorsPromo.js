"use strict";

import { customError, validateForm } from "./common/index.js";
import { getPhoneNumber, initPhoneInputs } from "./intell-input/intell-input.js";

const initSponsorsPromo = () => {
  const sponsorsPromoForm = document.getElementById("sponsorsPromoForm");
  if (!sponsorsPromoForm) return;

  let isSubmitting = false;

  const toggleMessage = (resp = 0) => {
    const sponsorFormContainer = document.querySelector(".emms__register-modal--sponsor-promo");
    const successMessage = document.querySelector(".emms__register-modal__window--success-message");
    if (resp === 200) {
      successMessage.classList.add("dp--modal");
      sponsorFormContainer.classList.add("dp--modal");
    } else {
      successMessage.classList.remove("dp--modal");
      sponsorFormContainer.classList.remove("dp--modal");
    }
  };

  const mapType = (type) => ({ sponsor: "Sponsor", mediaPartner: "Media Partner" })[type];

  sponsorsPromoForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const dataType = sponsorsPromoForm.dataset.sponsorType;
    if (!dataType || isSubmitting) return;

    isSubmitting = true;

    try {
      await initPhoneInputs(sponsorsPromoForm);
      if (!validateForm(sponsorsPromoForm)) return;

      const formData = new FormData(sponsorsPromoForm);
      const phoneInput = sponsorsPromoForm.querySelector('input[name="phone"]');
      const urlParams = new URLSearchParams(window.location.search);
      const sponsorData = {
        name: formData.get("name"),
        email: formData.get("email"),
        company: formData.get("company"),
        phone: getPhoneNumber(phoneInput),
        acceptPolicies: formData.get("privacy") === "true" ? true : null,
        acceptPromotions: formData.get("promotions") === "true" ? true : null,
        utm_source: urlParams.get("utm_source") || "direct",
        utm_campaign: urlParams.get("utm_campaign"),
        utm_content: urlParams.get("utm_content"),
        utm_term: urlParams.get("utm_term"),
        utm_medium: urlParams.get("utm_medium"),
        origin: urlParams.get("origin"),
        emms_ref: urlParams.get("emms_ref"),
        dataType,
      };

      sponsorsPromoForm.querySelector("button")?.classList.add("button--loading");
      const fetchResp = await fetch("./services/registerSponsor.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(sponsorData),
      });
      const resp = await fetchResp.json();
      if (resp === 200) {
        sponsorsPromoForm.reset();
        toggleMessage(resp);
      }
    } catch (error) {
      customError("Error en formulario Sponsors", error);
    } finally {
      isSubmitting = false;
      sponsorsPromoForm.querySelector("button")?.classList.remove("button--loading");
    }
  });

  document.addEventListener("click", (event) => {
    const target = event.target || event.srcElement;
    if (!target) return;

    if (target.hasAttribute("data-toggle") && target.getAttribute("data-toggle") == "emms__register-modal" && target.hasAttribute("data-target")) {
      const modalId = target.getAttribute("data-target");
      const dataType = target.getAttribute("data-type");
      sponsorsPromoForm.dataset.sponsorType = dataType;
      document.getElementById("sponsorType").innerText = mapType(dataType);
      document.getElementById(modalId).classList.add("open");
      document.querySelector("body").style.overflowY = "hidden";
      event.preventDefault();
    }

    if ((target.hasAttribute("data-dismiss") && target.getAttribute("data-dismiss") == "emms__register-modal") || target.classList.contains("emms__register-modal")) {
      const modal = document.querySelector('[class="emms__register-modal open"]');
      delete sponsorsPromoForm.dataset.sponsorType;
      modal?.classList.remove("open");
      document.querySelector("body").style.overflowY = "scroll";
      toggleMessage();
      event.preventDefault();
    }
  });
};

if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initSponsorsPromo, { once: true });
else initSponsorsPromo();
