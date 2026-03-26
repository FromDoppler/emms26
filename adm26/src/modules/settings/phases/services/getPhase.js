export const getPhase = async (currentEvent) => {
  try {
    const getPhaseUrl = "/adm25/server/modules/settings/getPhase.php?";
    const response = await fetch(
      getPhaseUrl +
        new URLSearchParams({
          event: currentEvent + "25",
        }),
    );
    const result = await response.json();
    return result;
  } catch (error) {
    console.log(error);
  }
};
