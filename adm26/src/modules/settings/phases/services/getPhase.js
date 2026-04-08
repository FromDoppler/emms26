import { ADMIN_BASE_PATH, getEventId } from "../../../../config/adminConfig.js";

export const getPhase = async (currentEvent) => {
  try {
    const getPhaseUrl = `${ADMIN_BASE_PATH}/server/modules/settings/getPhase.php`;
    const response = await fetch(
      `${getPhaseUrl}?${new URLSearchParams({
        event: getEventId(currentEvent),
      })}`,
    );

    if (!response.ok) {
      throw new Error(`Unable to fetch phase for ${currentEvent}: ${response.status}`);
    }

    const result = await response.json();
    return result;
  } catch (error) {
    console.log(error);
    return null;
  }
};
