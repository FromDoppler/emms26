"use strict";

import { isQADomain } from "./utils/certificateUtils.js";

const workshop = await import(`./workshop.js?v=${window.APP.VERSION}`);
const { downloadWorkshopCertificate, getUrlWorkshop } = workshop;

const buildCertificateUrl = (fullname, type) => {
  const encodeFullname = encodeURI(fullname);
  const domainUrl = isQADomain() ? `certificate-emms2025qa.php` : `certificate-emms2025.php`;
  return `https://textify.fromdoppler.com/${domainUrl}?fullname=${encodeFullname}&type=${type}`;
};

const downloadNormalCertificate = (fullname, type) => {
  const url = buildCertificateUrl(fullname, type);
  const iframe = document.createElement("iframe");
  iframe.style.display = "none";
  iframe.src = url;
  document.body.appendChild(iframe);
  iframe.onload = () => document.body.removeChild(iframe);
};

const forceDownload = async (fullname, type) => {
  const workshopType = getUrlWorkshop();
  try {
    if (type === "workshop" && workshopType) {
      await downloadWorkshopCertificate(fullname);
    } else {
      await downloadNormalCertificate(fullname, type);
    }
  } catch (error) {
    console.error("Error:", error);
    throw error;
  }
};

const handleButtonState = (submitButton, enable, showError) => {
  submitButton.setAttribute("data-disabled", enable ? "true" : "false");
  submitButton.classList.toggle("button--loading", enable);
  const errorSpan = document.querySelector("#certificateForm span");
  errorSpan.classList.toggle("showError", showError);
};

const submitCertificateWithoutForm = async (e, type, submitButton, userName) => {
  e.preventDefault();
  const isDisabled = submitButton.getAttribute("data-disabled") === "true";
  if (isDisabled) return false;

  handleButtonState(submitButton, true, false);
  try {
    await forceDownload(userName, type);
    return true;
  } catch (error) {
    console.error(error);
  } finally {
    handleButtonState(submitButton, false, false);
  }
};

const handleCertificateSubmit = async (e, type, submitButton) => {
  e.preventDefault();
  const certificateForm = document.getElementById("certificateForm");
  const formData = new FormData(certificateForm);
  const fullname = formData.get("fullname");
  const isDisabled = submitButton.getAttribute("data-disabled") === "true";

  if (isDisabled) return false;

  handleButtonState(submitButton, true, false);

  if (fullname.length < 2) {
    handleButtonState(submitButton, false, true);
    return false;
  }

  try {
    await forceDownload(fullname, type);
    certificateForm.reset();
    return true;
  } catch (error) {
    console.error(error);
  } finally {
    handleButtonState(submitButton, false, false);
  }
};

export { handleCertificateSubmit, submitCertificateWithoutForm };
