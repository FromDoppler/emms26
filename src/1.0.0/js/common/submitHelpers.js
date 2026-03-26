import { toHex } from "./index.js";

export const buildUserObject = (email, events) => ({
  userEmail: email,
  userEvents: events,
});

export const buildUserData = (payload = {}) => {
  const { name = null, email, phone = null, acceptPolicies = null, acceptPromotions = null, type, events, ...extra } = payload;
  const encodeEmail = toHex(JSON.stringify(buildUserObject(email, events)));

  const urlParams = new URLSearchParams(window.location.search);

  const utms = {
    utm_source: urlParams.get("utm_source") || "direct",
    utm_campaign: urlParams.get("utm_campaign"),
    utm_content: urlParams.get("utm_content"),
    utm_term: urlParams.get("utm_term"),
    utm_medium: urlParams.get("utm_medium"),
    origin: urlParams.get("origin"),
    emms_ref: urlParams.get("emms_ref"),
  };

  return {
    name,
    email,
    phone,
    encodeEmail,
    acceptPolicies,
    acceptPromotions,
    ...utms,
    type,
    events,
    ...extra,
  };
};

export const setEventInLocalStorage = (fetchType, encodeEmail) => {
  let events = localStorage.getItem("events");

  try {
    events = events ? JSON.parse(events) : [];
    if (!Array.isArray(events)) events = [events];
    events.push(fetchType);
  } catch {
    events = [fetchType];
  }

  if (!localStorage.getItem("dplrid")) {
    localStorage.setItem("dplrid", encodeEmail);
  }

  localStorage.setItem("events", JSON.stringify(events));
  localStorage.setItem("lastEventsUpdateTime", new Date().toString());

  return JSON.stringify(events);
};

export const extractFormData = (form) => {
  const formData = new FormData(form);
  const email = formData.get("email");
  const dialCode = document.querySelector(".iti__selected-dial-code")?.innerHTML || "";
  const phone = formData.get("phone").trim() ? dialCode + formData.get("phone") : null;

  return {
    name: formData.get("name"),
    email,
    phone,
    acceptPolicies: formData.get("privacy") === "true" || null,
    acceptPromotions: formData.get("promotions") === "true" || null,
  };
};

export const toggleButtonLoading = (form, isLoading) => {
  const button = form.querySelector("button");
  if (button) button.classList.toggle("button--loading", isLoading);
};

export const trackMetaPixel = (event) => {
  if (typeof fbq === "function") {
    fbq("track", event);
  }
};
