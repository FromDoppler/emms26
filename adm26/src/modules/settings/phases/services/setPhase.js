import { ADMIN_BASE_PATH, getEventId } from "../../../../config/adminConfig.js";

export const setPhase = async (currentEvent, selectedPhase, transition) => {
  try {
    const setPhaseUrl = `${ADMIN_BASE_PATH}/server/modules/settings/setPhase.php`;
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
    const setPhaseUrl = `${ADMIN_BASE_PATH}/server/modules/settings/setTransmission.php`;
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
