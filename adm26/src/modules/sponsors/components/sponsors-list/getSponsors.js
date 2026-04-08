import { ADMIN_BASE_PATH } from "../../../../config/adminConfig.js";

export const getSponsors = async (currentSponsorType) => {
  const getSponsorsUrl = `${ADMIN_BASE_PATH}/server/modules/sponsors/getSponsors.php?`;
  const response = await fetch(
    getSponsorsUrl +
      new URLSearchParams({
        sponsorType: currentSponsorType,
      }),
  );
  const result = await response.json();
  return result;
};
