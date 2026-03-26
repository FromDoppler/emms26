export const createCanShowModal = async () => {
  const version = window.VERSION || "1.0.0";
  const eventsModule = await import(`/src/${version}/js/enums/eventsType.enum.js`);
  const { eventsType } = eventsModule;

  const { modalIds } = await import("./modalIds.enum.js");

  const getUserEvents = () => {
    try {
      const raw = localStorage.getItem("events");
      return raw ? JSON.parse(raw) : [];
    } catch {
      return [];
    }
  };

  const canShowModal = (modalId) => {
    const events = getUserEvents();

    switch (modalId) {
      case modalIds.VIP:
        return !events.includes(eventsType.DIGITALTRENDSVIP);

      case modalIds.FORM:
        return events.includes(eventsType.ECOMMERCE) && !events.includes(eventsType.DIGITALTRENDS);

      case modalIds.EXTRA_DATA:
        return true;

      default:
        return true;
    }
  };

  return canShowModal;
};
