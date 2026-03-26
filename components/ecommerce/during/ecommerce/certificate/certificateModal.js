import { submitCertificate } from "./certificate.js";
const certificateCta = document.getElementById("certificateCta");
certificateCta.addEventListener("click", async (e) => {
  let submitSucceeded = false;
  try {
    submitSucceeded = await submitCertificate(e, "ecommerce", certificateCta);
  } catch (error) {
    console.error(error);
  }
  if (submitSucceeded) {
    document.getElementById("certificateModal").classList.toggle("open");
    document.body.style.overflowY = "scroll";
  }
});
