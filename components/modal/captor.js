import { openModal } from "./openModal.js";

const EXIT_TOP_THRESHOLD_PX = 8;
const EXIT_EDGE_THRESHOLD_PX = 8;
const SHOW_ONCE_PER_SESSION = true;

const initExitIntentCapture = async () => {
  const version = window.APP.VERSION;
  const { createCanShowModal } = await import(`./scripts/modalConditions.js?v=${encodeURIComponent(version)}`);
  const canShowModal = await createCanShowModal();

  document.querySelectorAll('.popup-modal[data-captor="1"]').forEach((modalEl) => setupExitIntentForModal(modalEl, canShowModal));
};

const setupExitIntentForModal = (modalEl, canShowModal) => {
  const modalId = modalEl.id;
  if (!modalId) return;

  const sessionKey = `exitIntentShown:${modalId}`;
  if (SHOW_ONCE_PER_SESSION && localStorage.getItem(sessionKey)) return;

  let lastMousePosition = null;

  const markShownThisSession = () => {
    localStorage.setItem(sessionKey, "1");
  };

  const removeExitIntentListeners = () => {
    document.removeEventListener("mousemove", handleMouseMove, true);
    document.removeEventListener("mouseout", handleMouseOut, true);
    document.removeEventListener("mouseleave", handleMouseLeave, true);
    document.removeEventListener("visibilitychange", handleVisibilityChange, true);
  };

  const triggerModalOnce = () => {
    if (!canShowModal(modalId)) return;
    openModal(modalId, {
      delay: 250,
      onOpened: () => {
        markShownThisSession();
        removeExitIntentListeners();
      },
    });
  };

  const handleMouseMove = (e) => {
    lastMousePosition = { x: e.clientX, y: e.clientY };
  };

  const handleMouseOut = (e) => {
    const related = e.relatedTarget || e.toElement;
    if (related) return; // sigue dentro del documento
    if (e.clientY <= EXIT_TOP_THRESHOLD_PX) triggerModalOnce();
  };

  const handleMouseLeave = (e) => {
    if (e.relatedTarget || e.toElement || !lastMousePosition) return;

    const { x, y } = lastMousePosition;
    const isNearViewportEdge = x <= EXIT_EDGE_THRESHOLD_PX || y <= EXIT_EDGE_THRESHOLD_PX || x >= window.innerWidth - EXIT_EDGE_THRESHOLD_PX || y >= window.innerHeight - EXIT_EDGE_THRESHOLD_PX;

    if (isNearViewportEdge) triggerModalOnce();
  };

  const handleVisibilityChange = () => {
    if (document.visibilityState === "hidden") triggerModalOnce();
  };

  const addExitIntentListeners = () => {
    document.addEventListener("mousemove", handleMouseMove, true);
    document.addEventListener("mouseout", handleMouseOut, true);
    document.addEventListener("mouseleave", handleMouseLeave, true);
    document.addEventListener("visibilitychange", handleVisibilityChange, true);
  };

  addExitIntentListeners();
};

document.addEventListener("DOMContentLoaded", () => {
  initExitIntentCapture().catch((err) => console.error("Exit intent init error:", err));
});
