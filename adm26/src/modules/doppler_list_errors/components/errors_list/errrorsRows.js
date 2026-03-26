import { getErrors } from "./getErrors.js";

export const errorsRows = async () => {
  const filteredErrors = await getErrors();
  const errorsList = document.getElementById("errorsList");
  filteredErrors && filteredErrors.length
    ? filteredErrors.map(
        (el, index) =>
          (errorsList.querySelector("tbody").innerHTML += `
                <tr key=${index}>
                    <td>
                        <span> ${index} </span>
                    </td>
                    <td>
                        <span> ${el.email} </span>
                    </td>
                    <td>
                    <span> ${el.list} </span>
                    </td>
                    <td>
                        <span> ${el.reason} </span>
                    </td>
                    <td>
                    <span> ${el.error_code} </span>
                </td>
                    <td>
                        <span> ${el.created_at} </span>
                    </td>
                </tr> `),
      )
    : (errorsList.querySelector("tbody").innerHTML = `
            <tr>
            <td colspan="6">
                No Data.
            </td>
            </tr>`);
};
