import { getPhase } from "./services/getPhase.js";
import { setPhase } from "./services/setPhase.js";
import { setTransmission } from "./services/setPhase.js";
import { sendRefresh } from "./services/sendRefresh.js";

export const showPhasesListPage = async () => {
  const phasesListUrl = "src/modules/settings/phases/phases-list.html";
  const response = await fetch(phasesListUrl);
  const phases = document.querySelector("phases");
  phases.innerHTML = await response.text();

  const ecommerceForm = document.getElementById("ecommerce_current_phase");
  ecommerceForm.addEventListener("submit", function (e) {
    sendDataCurrentPhase(e, "ecommerce");
  });

  const digitalTrendsForm = document.getElementById("digital-trends_current_phase");
  digitalTrendsForm.addEventListener("submit", function (e) {
    sendDataCurrentPhase(e, "digital-trends");
  });

  const ecommerceTransmissionForm = document.getElementById("ecommerce_transmission");
  ecommerceTransmissionForm.addEventListener("submit", function (e) {
    sendDataCurrentTransmission(e, "ecommerce");
  });

  const digitalTrendsTransmissionForm = document.getElementById("digital-trends_transmission");
  digitalTrendsTransmissionForm.addEventListener("submit", function (e) {
    sendDataCurrentTransmission(e, "digital-trends");
  });

  hideAlerts();
  checkRadiosPhase("ecommerce");
  checkRadiosPhase("digital-trends");
  checkRadiosTransmission("ecommerce");
  checkRadiosTransmission("digital-trends");
};

function hideAlerts() {
  const eco = document.getElementById("ecommerce_current-alert-success");
  eco.style.display = "none";
  const dt = document.getElementById("digital-trends_current-alert-success");
  dt.style.display = "none";
  const ecoLive = document.getElementById("ecommerce_transmission-alert-success");
  ecoLive.style.display = "none";
  const dtLive = document.getElementById("digital-trends_transmission-alert-success");
  dtLive.style.display = "none";
}

function showAlert(id) {
  const card = document.getElementById(id);
  card.style.display = "block";
  setTimeout(() => {
    card.style.display = "none";
  }, 2000);
}

const getTransition = (event) => {
  const isLive = document.getElementById(event + "_toggle-live").checked;
  if (isLive) return "live-on";
  return "live-off";
};

//setPhase
const sendDataCurrentPhase = async (e, event) => {
  const selectedPhase = document.querySelector('input[name="' + event + '_phase"]:checked').getAttribute("data-phase");

  let currentTransition;
  if (selectedPhase !== "during") {
    currentTransition = "live-off";
    document.getElementById(event + "_toggle-live").checked = false;
  } else currentTransition = getTransition(event);

  const transition = currentTransition;

  e.preventDefault();
  await setPhase(event, selectedPhase, transition);
  showAlert(event + "_current-alert-success");
  await sendRefresh();
};

//getPhase
const checkRadiosPhase = async (event) => {
  const objResult = await getPhase(event);

  document.getElementById(event + "_" + objResult.current_phase).checked = true;
  if (objResult.transition === "live-on") document.getElementById(event + "_toggle-live").checked = true;
  else document.getElementById(event + "_toggle-live").checked = false;
};

const sendDataCurrentTransmission = async (e, event) => {
  e.preventDefault();
  const transmission = document.querySelector('input[name="' + event + '_transmission"]:checked').value;
  await setTransmission(event, transmission);
  showAlert(event + "_transmission-alert-success");
  const objResult = await getPhase(event);
  if (objResult.transition === "live-on") {
    await sendRefresh();
  }
};

const checkRadiosTransmission = async (event) => {
  const objResult = await getPhase(event);
  document.getElementById(event + "_" + objResult.transmission).checked = true;
};
