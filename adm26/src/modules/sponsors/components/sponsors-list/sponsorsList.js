const sponsorsListComponentUrl = "src/modules/sponsors/components/sponsors-list/sponsors-list.html";
import { addSponsorButton } from "../sponsor-form/sponsorForm.js";
import { sponsorType } from "../../types/sponsorTypes.js";
import { sponsorsRows } from "./sponsorsRows.js";

export const showSponsorsPage = async (currentSponsorType) => {
  const response = await fetch(sponsorsListComponentUrl);
  const app = document.querySelector("app");
  app.innerHTML = await response.text();
  app.querySelector("#sponsorTitle").innerHTML += sponsorType[currentSponsorType];
  await addSponsorButton(currentSponsorType);
  await sponsorsRows(currentSponsorType);
};
