"use strict";

import { getSrcVersion } from "./common/version.js";

document.addEventListener("DOMContentLoaded", () => {
  const DEFAULT_COUNTRY = { countryName: "Argentina", countryCode: "AR" };
  const TARGET_COUNTRIES = ["AR", "BO", "CL", "CO", "CR", "CU", "DO", "EC", "ES", "GD", "GF", "GY", "HN", "HT", "JM", "MX", "NI", "PA", "PE", "PR", "PY", "SR", "SV", "UY", "VE"];

  // Los horarios se publican en hora de Argentina; moment-timezone resuelve el offset.
  const EVENT_TIMEZONE = "America/Argentina/Buenos_Aires";

  // Contenedores que todavia llevan el horario como copy en lugar de como dato.
  // ponytail: migrar esos templates a data-event-date/data-event-time + slots y
  // borrar renderLegacyContainer. Techo: la hora se lee del texto renderizado, asi
  // que cualquier cambio de copy que pierda el patron HH:mm corta la conversion.
  const LEGACY_CONTAINERS = [".emms__calendar__date__country", ".emms__calendar__list__item__country"];
  const LEGACY_EVENT_DATE = "2026-09-29";

  const SLOTS_SELECTOR = "[data-event-time]";
  const LEGACY_SELECTOR = LEGACY_CONTAINERS.map((selector) => `${selector}:not([data-event-time]) span`).join(", ");

  const HOUR_IN_TEXT = /\b(\d{1,2}):(\d{2})\b/;
  const HOUR_IN_DATA = /^(\d{1,2}):(\d{2})$/;
  const DATE_IN_DATA = /^\d{4}-\d{2}-\d{2}$/;

  const isTargetCountry = ({ countryCode }) => TARGET_COUNTRIES.includes(countryCode);

  const getCountryAndCode = async () => {
    try {
      const response = await fetch("/services/getCountryNameAndCode.php");
      const country = await response.json();
      return isTargetCountry(country) ? country : DEFAULT_COUNTRY;
    } catch (error) {
      console.error(error);
      return DEFAULT_COUNTRY;
    }
  };

  const createImgElement = (countryName, countryCode) => {
    const img = document.createElement("img");
    img.src = `/src/img/flags/${countryCode}.png`;
    img.alt = countryName;
    img.title = countryName;
    return img;
  };

  const getCountryLabel = (countryCode) => (countryCode === "AR" ? "ARG" : countryCode);

  const loadScriptAsync = (src) => {
    return new Promise((resolve, reject) => {
      const script = document.createElement("script");
      script.src = src;
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  };

  const loadMomentAndTimezoneScripts = async () => {
    const currentSrcVersion = getSrcVersion();

    try {
      await loadScriptAsync(`/src/${currentSrcVersion}/js/vendors/moment.min.js`);
      await loadScriptAsync(`/src/${currentSrcVersion}/js/vendors/moment-timezone-data.min.js`);
      return true;
    } catch (error) {
      console.error("Error scripts load:", error);
      return false;
    }
  };

  const initDateChanges = async () => {
    // Sin nada que actualizar no vale la pena bajar moment ni geolocalizar.
    if (!document.querySelector(`${SLOTS_SELECTOR}, ${LEGACY_SELECTOR}`)) return;

    if (!(await loadMomentAndTimezoneScripts())) return;

    const moment = window.moment;

    const getLocalDate = (eventDate, eventTime, userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone) => {
      return moment.tz(`${eventDate} ${eventTime}`, "YYYY-MM-DD HH:mm", EVENT_TIMEZONE).tz(userTimeZone);
    };

    const readEventTime = (value, pattern) => {
      const match = pattern.exec((value ?? "").trim());
      return match ? `${match[1].padStart(2, "0")}:${match[2]}` : null;
    };

    // Escribe en slots con nombre: la copy vive entera en el template.
    const renderSlotsContainer = (container, countryName, countryCode) => {
      const eventTime = readEventTime(container.dataset.eventTime, HOUR_IN_DATA);
      const eventDate = container.dataset.eventDate;

      if (!eventTime || !DATE_IN_DATA.test(eventDate ?? "")) {
        console.warn("newDate: data-event-date/data-event-time invalidos, se deja el horario del template", container);
        return;
      }

      const localDate = getLocalDate(eventDate, eventTime);

      const flagSlot = container.querySelector("[data-flag]");
      if (flagSlot) flagSlot.replaceChildren(createImgElement(countryName, countryCode));

      const codeSlot = container.querySelector("[data-country-code]");
      if (codeSlot) codeSlot.textContent = getCountryLabel(countryCode);

      const timeSlot = container.querySelector("[data-local-time]");
      if (timeSlot) timeSlot.textContent = `${localDate.format("h:mm")} ${localDate.hour() < 12 ? "a.m." : "p.m."}`;
    };

    const renderLegacyContainer = (span, countryName, countryCode) => {
      const eventTime = readEventTime(span.textContent, HOUR_IN_TEXT);

      if (!eventTime) {
        console.warn("newDate: no se encontro un horario HH:mm en el contenedor legacy", span);
        return;
      }

      const localDate = getLocalDate(LEGACY_EVENT_DATE, eventTime);
      const minutes = localDate.minute().toString().padStart(2, "0");

      span.replaceChildren(createImgElement(countryName, countryCode));
      span.append(`(${getCountryLabel(countryCode)}) ${localDate.hour()}:${minutes}`);
    };

    const { countryName, countryCode } = await getCountryAndCode();

    document.querySelectorAll(SLOTS_SELECTOR).forEach((container) => renderSlotsContainer(container, countryName, countryCode));
    document.querySelectorAll(LEGACY_SELECTOR).forEach((span) => renderLegacyContainer(span, countryName, countryCode));
  };

  initDateChanges();
});
