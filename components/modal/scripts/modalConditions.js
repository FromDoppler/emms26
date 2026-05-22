export const createCanShowModal = async () => {
  const version = window.APP.VERSION;

  const versionQuery = `?v=${encodeURIComponent(version)}`;

  const eventsModule = await import(`/src/${version}/js/enums/eventsType.enum.js${versionQuery}`);
  const { eventsType } = eventsModule;

  const { modalIds } = await import(`./modalIds.enum.js${versionQuery}`);

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
