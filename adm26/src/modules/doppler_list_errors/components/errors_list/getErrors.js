import { ADMIN_BASE_PATH } from "../../../../config/adminConfig.js";

export const getErrors = async () => {
  const getErrorsUrl = `${ADMIN_BASE_PATH}/server/modules/doppler_list_errors/getDopplerListErrors.php`;
  const response = await fetch(getErrorsUrl);
  const result = await response.json();
  return result;
};
