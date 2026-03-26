import { showErrorsPage } from "../doppler_list_errors/components/errors_list/errorsList.js";

export const showStatisticsPage = async () => {
  const statisticsComponentUrl = "src/modules/statistics/statistics.html";
  const response = await fetch(statisticsComponentUrl);
  const app = document.querySelector("app");
  app.innerHTML = await response.text();
  showErrorsPage();
};
