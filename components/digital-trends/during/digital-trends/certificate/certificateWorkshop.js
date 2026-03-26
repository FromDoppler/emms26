const certificate = await import(`./certificate2.js?v=${window.APP.VERSION}`);
const { handleCertificateSubmit } = certificate;

const certificateCta = document.getElementById("certificateCta");
const certificateModal = document.getElementById("certificateModal");
const closeModalBtn = document.querySelector('[data-dismiss="emms__certificate-modal"]');
const certificateForm = document.getElementById("certificateForm");
const certificateButton = document.getElementById("certificateWorkshop");

if (certificateCta) {
  certificateCta.addEventListener("click", (e) => {
    e.preventDefault();
    certificateModal.classList.add("open");
    document.body.style.overflowY = "hidden";
  });
}

if (closeModalBtn) {
  closeModalBtn.addEventListener("click", (e) => {
    e.preventDefault();
    certificateModal.classList.remove("open");
    document.body.style.overflowY = "scroll";
  });
}

if (certificateButton && certificateForm) {
  certificateButton.addEventListener("click", async (e) => {
    e.preventDefault();

    try {
      const submitSucceeded = await handleCertificateSubmit(e, "workshop", certificateButton);
      if (submitSucceeded) {
        certificateModal.classList.remove("open");
        document.body.style.overflowY = "scroll";
      }
    } catch (error) {
      console.error("Error al enviar el certificado:", error);
    }
  });
}
