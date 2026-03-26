"use strict";
import { toHex, getEncodeURLEmail } from "./common/index.js";

const encodeUser = getEncodeURLEmail();

const _setLocalStorageEvents = (userEvents) => {
  localStorage.setItem("events", userEvents);
  localStorage.setItem("lastEventsUpdateTime", new Date());
};

const checkEncodeUrl = () => {
  if (!encodeUser) {
    return;
  }

  //Check if it is an old user that was saved before the new JSON logic
  if (encodeUser.includes("{")) {
    const userData = JSON.parse(encodeUser);
    const userEvents = userData.userEvents;
    const localStorageEvents = JSON.parse(localStorage.getItem("events"));
    if (localStorageEvents) {
      if (userEvents.length > localStorageEvents.length) {
        _setLocalStorageEvents(userEvents);
      } else if (userEvents.length === localStorageEvents.length && JSON.stringify(userEvents) != JSON.stringify(localStorageEvents)) {
        const mergedEvents = userEvents.concat(localStorageEvents);
        _setLocalStorageEvents(mergedEvents);
      }
    } else {
      localStorage.setItem("dplrid", toHex(userData.userEmail));
      _setLocalStorageEvents(userEvents);
    }
  }
};

const userRegisteredInEvent = (event) => {
  let eventsArray = localStorage.getItem("events");

  if (eventsArray) {
    try {
      // Try to parse the value as JSON (array)
      eventsArray = JSON.parse(eventsArray);

      if (!Array.isArray(eventsArray)) {
        // If it is not an array, create a new array and add the value
        eventsArray = [eventsArray];
        localStorage.setItem("events", JSON.stringify(eventsArray));
      }
    } catch (error) {
      // If it cannot be parsed as JSON, assume it is a single value and create a new array
      eventsArray = [eventsArray];
      localStorage.setItem("events", JSON.stringify(eventsArray));
    }

    const searchedEvent = eventsArray.find((userEvent) => userEvent === event);
    return searchedEvent !== undefined;
  }
  return false;
};

const hiddenOrShowUserUI = (event) => {
  const hiddenElements = document.querySelectorAll(".eventHiddenElements");
  const showElements = document.querySelectorAll(".eventShowElements");
  showElements.forEach((element) => (element.style.display = "none"));
  if (!userRegisteredInEvent(event)) return;
  hiddenElements.forEach((element) => (element.style.display = "none"));
  showElements.forEach((element) => {
    if (element.classList.contains("inline-block")) {
      element.style.display = "inline-block";
    } else {
      element.style.display = "block";
    }
  });
};

const processEventCards = (cards, eventName) => {
  if (userRegisteredInEvent(eventName)) {
    cards.forEach((card) => {
      card.querySelector(".not--loged").classList.add("nodisplay--card");
      card.querySelector(".loged").classList.add("display--card");
    });
  }
};

const registerEventsCardsCheck = () => {
  const ecommerceCards = document.querySelectorAll(".ecommerceCard");
  const digitalTCards = document.querySelectorAll(".digitalTCard");
  processEventCards(ecommerceCards, window.APP.EVENTS.EVENTCODES.ECOMMERCE);
  processEventCards(digitalTCards, window.APP.EVENTS.EVENTCODES.DIGITALTRENDS);
};

export { checkEncodeUrl, userRegisteredInEvent, hiddenOrShowUserUI, registerEventsCardsCheck };
