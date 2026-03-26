import { toHex } from "./decodeEmail.js";
import { validateSimpleForm } from "./formsValidators.js";
import { setEventInLocalStorage } from "./submitHelpers.js";

const ecommerceAlreadyAccountForm = document.getElementById("alreadyAccountForm");

const getUserByEmail = async () => {
  const url = "./services/getRegisteredByEmail.php";
  const formData = new FormData(ecommerceAlreadyAccountForm);
  const userEmail = formData.get("email");
  try {
    const resp = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email: userEmail }),
    });

    if (!resp.ok) {
      throw new Error(`Error try getUserByEmail: ${resp.status}`);
    }

    const userResponse = await resp.json();
    return userResponse;
  } catch (error) {
    console.error("Error in getUserByEmail:", error);
  }
};

const redirectToRegisteredPage = () => {
  let targetUrl;

  switch (window.location.pathname) {
    case "/sponsors":
      targetUrl = "/sponsors-registrado";
      break;
    case "/digital-trends":
      targetUrl = "/digital-trends-registrado";
      break;
    default:
      targetUrl = "/ecommerce-registrado";
      break;
  }

  window.location.href = window.APP.utils.addParams(targetUrl);
};

const localEventsMap = {
  ecommerce: window.APP.EVENTS.EVENTCODES.ECOMMERCE,
  "ecommerce-vip": window.APP.EVENTS.EVENTCODES.ECOMMERCEVIP,
  "digital-trends": window.APP.EVENTS.EVENTCODES.DIGITALTRENDS,
  "digital-trends-vip": window.APP.EVENTS.EVENTCODES.DIGITALTRENDSVIP,
};

const filterEvents = (events) => {
  return Object.entries(events)
    .filter(([eventName, eventValue]) => eventValue === 1)
    .map(([eventName, _]) => localEventsMap[eventName]);
};

const setMultipleEvents = (events, userEmail) => {
  events.forEach((event) => {
    setEventInLocalStorage(event, toHex(userEmail));
  });
};

const swichForm = (form) => {
  const alreadyAccountForm = document.getElementById("alreadyAccountForm");
  alreadyAccountForm.classList.toggle("dp--none");
  form.classList.toggle("dp--none");
};

const swichFormListener = (form) => {
  const switchButton = document.getElementById("swith");
  switchButton.addEventListener("click", () => {
    swichForm(form);
  });
};

const clearErrorListener = (form, errorSpan) => {
  const emailInput = form.querySelector("#email");
  emailInput.addEventListener("input", () => {
    errorSpan.classList.remove("display");
  });
};

const setUserNotExistError = (form) => {
  const errorSpan = document.querySelector("#email + .alreadyAccountForm__custom-error");
  if (errorSpan) {
    errorSpan.classList.add("display");
  } else {
    const errorSpan = document.createElement("span");
    errorSpan.className = "alreadyAccountForm__custom-error display";
    errorSpan.textContent = "Ouch, parece que no te has registrado con ese correo… Asegúrate de que esté bien redactado o ";
    const linkB = document.createElement("b");
    linkB.textContent = "dirígete aquí";
    linkB.addEventListener("click", () => {
      const switchButton = document.getElementById("swith");
      switchButton.click();
    });
    errorSpan.appendChild(linkB);
    const emailElement = document.querySelector(".alreadyAccountForm #email");
    emailElement.parentNode.insertBefore(errorSpan, emailElement.nextSibling);
    clearErrorListener(form, errorSpan);
  }
};

const alreadyAccountListener = () => {
  ecommerceAlreadyAccountForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!validateSimpleForm(ecommerceAlreadyAccountForm)) return;
    ecommerceAlreadyAccountForm.querySelector("button").classList.add("button--loading");

    const resp = await getUserByEmail();
    const events = filterEvents(resp);
    const userEmail = resp.email;
    if (userEmail) {
      setMultipleEvents(events, userEmail);
      redirectToRegisteredPage();
    } else {
      setUserNotExistError(ecommerceAlreadyAccountForm);
    }
    ecommerceAlreadyAccountForm.querySelector("button").classList.remove("button--loading");
  });
};

export { swichFormListener, alreadyAccountListener };
