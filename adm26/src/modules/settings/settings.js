import { showPhasesListPage } from "./phases/showPhasesList.js";

export const showSettingsPage = async () => {
  const settingsComponentUrl = "src/modules/settings/settings.html";
  const response = await fetch(settingsComponentUrl);
  const app = document.querySelector("app");
  app.innerHTML = await response.text();
  await showPhasesListPage();
};
