"use strict";
import { submitCertificate } from "./common/certificate.js";

const startCertificateWorkshop = () => {
  document.addEventListener("DOMContentLoaded", async () => {
    const toggleSpinner = () => {
      const spinner = document.getElementById("spinner");
      spinner.classList.toggle("visible");
    };

    const changeUserMessage = (userName, certificateContainer) => {
      const messageElement = certificateContainer.querySelector("p");
      messageElement.innerHTML = `Hola <strong>${userName}!</strong> Haz clic en el siguiente botón para <br> comenzar la descarga`;
    };

    const hideInput = (certificateContainer) => {
      const inputElement = certificateContainer.querySelector("#certificateForm input");
      inputElement.style.display = "none";
    };

    const changeUserUI = (userName) => {
      const certificateContainer = document.querySelector(".emms__certificate-download");
      changeUserMessage(userName, certificateContainer);
      hideInput(certificateContainer);
    };

    const initializeUI = () => {
      const certificateWorkshopButton = document.getElementById("certificateWorkshop");

      certificateWorkshopButton.addEventListener("click", async (e) => {
        toggleSpinner();
        await submitCertificate(e, "workshop", certificateWorkshopButton);
        toggleSpinner();
      });
    };
    initializeUI();
  });
};

startCertificateWorkshop();
