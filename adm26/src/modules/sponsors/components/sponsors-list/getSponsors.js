export const getSponsors = async (currentSponsorType) => {
  const getSponsorsUrl = "/adm25/server/modules/sponsors/getSponsors.php?";
  const response = await fetch(
    getSponsorsUrl +
      new URLSearchParams({
        sponsorType: currentSponsorType,
      }),
  );
  const result = await response.json();
  return result;
};
