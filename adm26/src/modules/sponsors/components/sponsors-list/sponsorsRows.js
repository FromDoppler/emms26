import { getSponsors } from "./getSponsors.js";
import { showSponsorsPage } from "./sponsorsList.js";
import { showSponsorForm } from "../sponsor-form/sponsorForm.js";
const removeSponsor = async (sponsorId, currentSponsorType) => {
  const removeSponsorUrl = "/adm25/server/modules/sponsors/removeSponsor.php";
  const formData = new FormData();
  formData.append("sponsorId", sponsorId);

  await fetch(removeSponsorUrl, {
    method: "post",
    body: formData,
  });
  await showSponsorsPage(currentSponsorType);
};

const btnRemoveSponsorListener = (currentSponsorType) => {
  const removeBtn = document.getElementsByName("removeSponsor");
  removeBtn.forEach((el) => {
    el.addEventListener("click", async () => {
      const removeId = el.dataset.removeid;
      await removeSponsor(removeId, currentSponsorType);
    });
  });
};

const btnEditSponsorListener = (currentSponsorType) => {
  const editBtn = document.getElementsByName("editSponsor");
  editBtn.forEach((el) => {
    el.addEventListener("click", async () => {
      const objSponsor = JSON.parse(el.dataset.editid);
      await showSponsorForm(currentSponsorType, objSponsor);
    });
  });
};

export const sponsorsRows = async (currentSponsorType) => {
  const filteredSponsors = await getSponsors(currentSponsorType);
  const sponsorsList = document.getElementById("sponsorsList");
  filteredSponsors && filteredSponsors.length
    ? filteredSponsors.map(
        (el, index) => (
          (sponsorsList.querySelector("tbody").innerHTML += `
                <tr key=${index}>
                    <td>
                        <span> ${index} </span>
                    </td>
                    <td>
                        <span> ${el.name_company} </span>
                    </td>
                    <td>
                        <span> <img src="server/modules/sponsors/uploads/${el.logo_company}" width="65"> </span>
                    </td>
                    <td>
                        <span> ${el.priority_home} </span>
                    </td>
                    <td>
                        <span> ${el.priority_card} </span>
                    </td>
                    <td>
                        <span> <img src="server/modules/sponsors/uploads/${el.image_landing}" width="65"> </span>
                    </td>
                    <td>
                        <button type="button" name="editSponsor" data-editid='${JSON.stringify(el)}' class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"></path>
            </svg>

                        </button>
                        <button type="button" name="removeSponsor" data-removeid="${el.sponsor_id}" class="btn btn-outline-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"></path>
            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"></path>
            </svg>
                        </button>
                    </td>
                </tr> `),
          btnRemoveSponsorListener(currentSponsorType),
          btnEditSponsorListener(currentSponsorType)
        ),
      )
    : (sponsorsList.querySelector("tbody").innerHTML = `
            <tr>
            <td colspan="6">
                No Data.
            </td>
            </tr>`);
};
