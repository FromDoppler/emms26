import { createDownloadLink, isQADomain } from "./utils/certificateUtils.js";

const WORKSHOPS = [
  "anacirujano-KPL482",
  "pablorodriguez-XZM930",
  "alvarolopezherrera-QTN547",
  "fernandotellado-RGW825",
  "natzirturrado-JHV621",
  "marianokhatcherian-DFY308",
  "mariamarques-WER916",
  "xiscolopez-BTP764",
  "amandabozza-LQN582",
  "doppler-SCV409",
  "getlinko-ZHX273",
];

export const getUrlWorkshop = () => WORKSHOPS.find((workshop) => window.location.href.includes(workshop)) || false;

const buildWorkshopUrl = (fullname, workshopType) => {
  const encodeFullname = encodeURI(fullname);
  const domainUrl = isQADomain() ? `certificate-emms2025qa.php` : `certificate-emms2025.php`;
  return `https://textify.fromdoppler.com/${domainUrl}?fullname=${encodeFullname}&type=workshop&workshoptype=${encodeURI(workshopType)}`;
};

export const downloadWorkshopCertificate = (fullname) => {
  const workshopType = getUrlWorkshop();
  if (!workshopType) return;

  const url = buildWorkshopUrl(fullname, workshopType);
  const iframe = document.createElement("iframe");
  iframe.style.display = "none";
  iframe.src = url;
  document.body.appendChild(iframe);
  iframe.onload = () => document.body.removeChild(iframe);
};
