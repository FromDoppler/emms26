import { ADMIN_BASE_PATH } from "../../../../config/adminConfig.js";

export const sendRefresh = async () => {
  try {
    const sendRefreshUrl = `${ADMIN_BASE_PATH}/server/modules/settings/sendRefresh.php`;
    await fetch(sendRefreshUrl, {
      method: "post",
      headers: {
        "Access-Control-Allow-Origin": "*",
      },
    });
  } catch (error) {
    console.log(error);
  }
};
