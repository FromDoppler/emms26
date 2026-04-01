const buildAdminUrl = (path) => `${window.ADM_CONFIG.basePath}/${path.replace(/^\/+/, "")}`;
const getEventId = (eventKey) => window.ADM_CONFIG.eventIds[eventKey] ?? eventKey;

export const getPhase = async (currentEvent) => {
  try {
    const getPhaseUrl = buildAdminUrl("server/modules/settings/getPhase.php");
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
