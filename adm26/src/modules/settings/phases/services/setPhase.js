const buildAdminUrl = (path) => `${window.ADM_CONFIG.basePath}/${path.replace(/^\/+/, "")}`;
const getEventId = (eventKey) => window.ADM_CONFIG.eventIds[eventKey] ?? eventKey;

export const setPhase = async (currentEvent, selectedPhase, transition) => {
  try {
    const setPhaseUrl = buildAdminUrl("server/modules/settings/setPhase.php");
    const formData = new FormData();
    formData.append("event", getEventId(currentEvent));
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
    const setPhaseUrl = buildAdminUrl("server/modules/settings/setTransmission.php");
    const formData = new FormData();
    formData.append("event", getEventId(currentEvent));
    formData.append("transmission", transmission);

    await fetch(setPhaseUrl, {
      method: "post",
      body: formData,
    });
  } catch (error) {
    console.log(error);
  }
};
