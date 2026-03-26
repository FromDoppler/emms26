"use strict";

import { customError, validateForm } from "./common/index.js";

document.addEventListener("click", (e) => {
  e = e || window.event;
  const target = e.target || e.srcElement;
  const sponsorsPromoForm = document.getElementById("sponsorsPromoForm");

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

  const mapType = (type) => {
    const mapTypes = {
      sponsor: "Sponsor",
      mediaPartner: "Media Partner",
    };
    return mapTypes[type];
  };

  const submitForm = async (e, dataType) => {
    e.preventDefault();
    const endPoint = "./services/registerSponsor.php";
    const formData = new FormData(sponsorsPromoForm);
    const dialCode = document.querySelector(".iti__selected-dial-code").innerHTML;

    const urlParams = new URLSearchParams(window.location.search);

    const sponsorData = {
      name: formData.get("name"),
      email: formData.get("email"),
      company: formData.get("company"),
      phone: formData.get("phone").trim() !== "" ? dialCode + formData.get("phone") : null,
      acceptPolicies: formData.get("privacy") === "true" ? true : null,
      acceptPromotions: formData.get("promotions") === "true" ? true : null,
      utm_source: urlParams.get("utm_source") || "direct",
      utm_campaign: urlParams.get("utm_campaign"),
      utm_content: urlParams.get("utm_content"),
      utm_term: urlParams.get("utm_term"),
      utm_medium: urlParams.get("utm_medium"),
      origin: urlParams.get("origin"),
      emms_ref: urlParams.get("emms_ref"),
      dataType: dataType,
    };

    const isValidForm = validateForm(sponsorsPromoForm);
    if (isValidForm) {
      sponsorsPromoForm.querySelector("button").classList.add("button--loading");
      try {
        const fetchResp = await fetch(endPoint, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(sponsorData),
        });
        const resp = await fetchResp.json();
        if (resp === 200) {
          sponsorsPromoForm.reset();
          toggleMessage(resp);
        }
      } catch (error) {
        customError("Fetch error", error);
      }
      sponsorsPromoForm.querySelector("button").classList.remove("button--loading");
    }
  };

  let submitEventListener = null;

  if (target.hasAttribute("data-toggle") && target.getAttribute("data-toggle") == "emms__register-modal") {
    if (target.hasAttribute("data-target")) {
      const m_ID = target.getAttribute("data-target");
      const dataType = target.getAttribute("data-type");
      const sponsortType = document.getElementById("sponsorType");
      sponsortType.innerText = mapType(dataType);
      submitEventListener = (e) => submitForm(e, dataType);
      sponsorsPromoForm.addEventListener("submit", submitEventListener);
      document.getElementById(m_ID).classList.add("open");
      document.querySelector("body").style.overflowY = "hidden";
      e.preventDefault();
    }
  }

  if ((target.hasAttribute("data-dismiss") && target.getAttribute("data-dismiss") == "emms__register-modal") || target.classList.contains("emms__register-modal")) {
    const modal = document.querySelector('[class="emms__register-modal open"]');
    if (submitEventListener) {
      sponsorsPromoForm.removeEventListener("submit", submitEventListener);
    }
    modal.classList.remove("open");
    document.querySelector("body").style.overflowY = "scroll";
    toggleMessage();
    e.preventDefault();
  }
});
