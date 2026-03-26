export const setPhase = async (currentEvent, selectedPhase, transition) => {
  try {
    const setPhaseUrl = "/adm25/server/modules/settings/setPhase.php";
    const formData = new FormData();
    formData.append("event", currentEvent + "25");
    formData.append("phase", selectedPhase);
    formData.append("transition", transition);

    await fetch(setPhaseUrl, {
      method: "post",
      body: formData,
    });
  } catch (error) {
    console.log(error);
  }
};

export const setTransmission = async (currentEvent, transmission) => {
  try {
    const setPhaseUrl = "/adm25/server/modules/settings/setTransmission.php";
    const formData = new FormData();
    formData.append("event", currentEvent + "25");
    formData.append("transmission", transmission);

    await fetch(setPhaseUrl, {
      method: "post",
      body: formData,
    });
  } catch (error) {
    console.log(error);
  }
};
