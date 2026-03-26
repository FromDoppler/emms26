"use strict";

import { validateForm } from "./formsValidators.js";
import { toHex, fromHex } from "./decodeEmail.js";
import { buildUserData, setEventInLocalStorage, extractFormData, toggleButtonLoading, trackMetaPixel } from "./submitHelpers.js";

const sendUserData = async (userData) => {
  const endPoint = "./services/register.php";

  try {
    const fetchResp = await fetch(endPoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(userData),
    });

    if (fetchResp.ok) {
      const data = await fetchResp
        .clone()
        .json()
        .catch(() => null);
      if (data?.is_new === true) trackMetaPixel("CompleteRegistration");
    }

    return { fetchResp, encodeEmail: userData.encodeEmail };
  } catch (error) {
    console.log("Fetch error", error);
    return null;
  }
};

const submitFormFetch = async (form, fetchType) => {
  if (!validateForm(form)) return;

  const { name, email, phone, acceptPolicies, acceptPromotions } = extractFormData(form);
  const events = setEventInLocalStorage(fetchType, toHex(email));

  const userData = buildUserData({
    name,
    email,
    phone,
    acceptPolicies,
    acceptPromotions,
    type: fetchType,
    events,
  });

  toggleButtonLoading(form, true);
  const result = await sendUserData(userData);
  toggleButtonLoading(form, false);

  return result;
};

const submitWithoutForm = async (fetchType) => {
  const userEmail = localStorage.getItem("dplrid");
  if (!userEmail) return;

  const events = setEventInLocalStorage(fetchType, userEmail);

  const userData = buildUserData({
    email: fromHex(userEmail),
    type: fetchType,
    events,
  });

  return await sendUserData(userData);
};

const submitModalForm = async (form, fetchType, formOrigin = null) => {
  const formData = new FormData(form);
  const toNullIfEmpty = (val) => {
    const stringValue = (val ?? "").toString().trim();
    return stringValue === "" ? null : stringValue;
  };

  const jobPosition = toNullIfEmpty(formData.get("jobPosition"));
  const company = toNullIfEmpty(formData.get("company"));
  const website = toNullIfEmpty(formData.get("website"));
  const emailPlatform = toNullIfEmpty(formData.get("emailPlatform"));

  const encodedEmail = localStorage.getItem("dplrid");
  if (!encodedEmail) {
    console.warn("No hay dplrid en localStorage: no puedo actualizar el registro");
    return null;
  }

  const userData = buildUserData({
    email: fromHex(encodedEmail),
    type: fetchType,
    jobPosition,
    company,
    website,
    emailPlatform,
    formOrigin,
  });
  toggleButtonLoading(form, true);
  const result = await sendUserData(userData);
  toggleButtonLoading(form, false);

  return result;
};

export { submitFormFetch, submitWithoutForm, submitModalForm };
