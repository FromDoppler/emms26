const sendRefreshUrl = "/adm25/server/modules/settings/sendRefresh.php";

export const sendRefresh = async () => {
  try {
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
