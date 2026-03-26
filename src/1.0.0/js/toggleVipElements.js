const getLocalStorageEvents = () => {
  let localStorageEvents = localStorage.getItem("events");
  return localStorageEvents ? JSON.parse(localStorageEvents) : [];
};

const isVipUser = (eventType) => {
  const events = getLocalStorageEvents();
  return events.some((event) => event === eventType);
};

const toggleVipElements = () => {
  const vipElements = document.querySelectorAll(".hidden--vip, .show--vip");
  vipElements.forEach((element) => {
    element.classList.add("toogle");
  });
};

const toggleVipEcommerceElements = () => {
  const isEcommerceVip = isVipUser(window.APP.EVENTS.EVENTCODES.ECOMMERCEVIP);
  const academyBanner = document.getElementById("aprende-con-doppler");

  if (isEcommerceVip) {
    toggleVipElements();
  } else if (academyBanner) {
    academyBanner.style.display = "none";
  }
};

const toggleVipDigitalTrendsElements = () => {
  const isDTVip = isVipUser(window.APP.EVENTS.EVENTCODES.DIGITALTRENDSVIP);
  if (isDTVip) {
    toggleVipElements();
  }
};

export { toggleVipEcommerceElements, toggleVipDigitalTrendsElements };
