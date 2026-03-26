import { handleCertificateSubmit } from "./certificate2.js";
const certificateCta = document.getElementById("certificateCta");
const certificateModal = document.getElementById("certificateModal");
const closeModalBtn = document.querySelector('[data-dismiss="emms__certificate-modal"]');

certificateCta.addEventListener("click", async (e) => {
  let submitSucceeded = false;
  try {
    submitSucceeded = await handleCertificateSubmit(e, "digital-trends", certificateCta);
  } catch (error) {
    console.error(error);
  }
  if (submitSucceeded) {
    document.getElementById("certificateModal").classList.toggle("open");
    document.body.style.overflowY = "scroll";
  }
});

if (closeModalBtn) {
  closeModalBtn.addEventListener("click", (e) => {
    e.preventDefault();
    certificateModal.classList.remove("open");
    document.body.style.overflowY = "scroll";
  });
}
