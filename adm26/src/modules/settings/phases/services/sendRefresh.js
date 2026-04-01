const buildAdminUrl = (path) => `${window.ADM_CONFIG.basePath}/${path.replace(/^\/+/, "")}`;

export const sendRefresh = async () => {
  try {
    const sendRefreshUrl = buildAdminUrl("server/modules/settings/sendRefresh.php");
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
