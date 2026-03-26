const getErrorsUrl = "src/modules/doppler_list_errors/components/errors_list/errorsList.html";
import { errorsRows } from "./errrorsRows.js";

export const showErrorsPage = async () => {
  const response = await fetch(getErrorsUrl);
  const app = document.querySelector("errorModule");
  app.innerHTML = await response.text();
  await errorsRows();
};
