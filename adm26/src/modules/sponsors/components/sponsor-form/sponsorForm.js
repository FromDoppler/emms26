import { sponsorType } from "../../types/sponsorTypes.js";
import { id_field_col_array } from "./hiddenFieldList.js";
import { showSponsorsPage } from "../sponsors-list/sponsorsList.js";

const topFunction = () => {
  document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
};

const submitSponsorForm = (currentSponsorType, sponsorId) => {
  const form = document.querySelector("form");
  form.addEventListener(
    "submit",
    async function (event) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add("was-validated");
      topFunction();
      if (form.checkValidity()) {
        await addSponsor(currentSponsorType, sponsorId);
        await showSponsorsPage(currentSponsorType);
      }
    },
    false,
  );
};

function getRandomFileName(fileName) {
  const ext = fileName.substr(fileName.lastIndexOf(".") + 1);
  const timestamp = new Date().toISOString().replace(/[-:.]/g, "");
  const random = ("" + Math.random()).substring(2, 8);
  const randomFileName = timestamp + random + "." + ext;
  return randomFileName;
}

const formObj = () => {
  const elements = document.querySelector("form").elements;
  const myObject = {};
  Array.prototype.forEach.call(elements, (element) => {
    if (element.id.length !== 0) {
      if (element.type === "file") {
        if (element.value.length !== 0 && element.value !== "undefined") {
          myObject[element.id] = getRandomFileName(element.files[0].name);
        }
      } else if (element.type === "checkbox") {
        if (element.checked) myObject[element.id] = "1";
        else myObject[element.id] = "0";
      } else {
        myObject[element.id] = element.value;
      }
    }
  });
  return myObject;
};

const addSponsor = async (currentSponsorType, sponsorId) => {
  try {
    const sponsorObj = formObj();
    sponsorObj.sponsor_id = sponsorId;
    sponsorObj.sponsor_type = currentSponsorType;
    const inputFiles = document.querySelectorAll('input[type="file"]');
    const arrayFiles = [...inputFiles];
    const formData = new FormData();

    arrayFiles.forEach((inputFile) => {
      if (inputFile.files[0]) formData.append(inputFile.id, inputFile.files[0], sponsorObj[inputFile.id]);
    });

    const str = JSON.stringify(sponsorObj);
    formData.append("str", str);

    const addSponsorUrl = "/adm25/server/modules/sponsors/addSponsor.php";

    await fetch(addSponsorUrl, {
      method: "post",
      body: formData,
    });
  } catch (error) {
    console.log(error);
  }
};

const loadSponsorData = (objSponsor) => {
  const elements = document.querySelector("form").elements;
  Array.prototype.forEach.call(elements, (element) => {
    if (element.type === "checkbox") {
      if (objSponsor[element.id] === "1") element.checked = true;
    } else if (element.type !== "file") {
      element.value = objSponsor[element.id];
    } else if (element.type === "file") {
      const newlabel = document.createElement("Label");
      newlabel.setAttribute("for", element.id);
      newlabel.innerHTML = "reemplazar";
      newlabel.classList.add("btn");
      newlabel.classList.add("btn-primary");
      element.parentNode.insertBefore(newlabel, element.nextSibling);
      element.hidden = true;
      element.required = false;
    }
  });
};
export const showSponsorForm = async (currentSponsorType, objSponsor) => {
  const sponsorsFormComponentUrl = "src/modules/sponsors/components/sponsor-form/sponsor-form.html";
  const response = await fetch(sponsorsFormComponentUrl);
  const app = document.querySelector("app");
  app.innerHTML = await response.text();
  app.querySelector("#sponsorTitle").innerHTML += sponsorType[currentSponsorType];
  removeFieldsBySponsorType(currentSponsorType);
  if (typeof objSponsor !== "undefined") loadSponsorData(objSponsor);
  await submitSponsorForm(currentSponsorType, objSponsor?.sponsor_id);
};
export const addSponsorButton = async (currentSponsorType) => {
  const element = document.getElementById("addSponsorButton");

  element.addEventListener("click", async () => {
    showSponsorForm(currentSponsorType);
  });
};

const removeFieldsBySponsorType = (currentSponsorType) => {
  id_field_col_array.forEach((element) => {
    if (element.hiddenType.includes(currentSponsorType)) document.getElementById(element.col).remove();
  });
};
