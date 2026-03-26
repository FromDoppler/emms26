import { openModal } from "./openModal.js";
import { createCanShowModal } from "./scripts/modalConditions.js";

const EXIT_TOP_THRESHOLD_PX = 8;
const SHOW_ONCE_PER_SESSION = true;

const initExitIntentCapture = async () => {
  const canShowModal = await createCanShowModal();

  document.querySelectorAll('.popup-modal[data-captor="1"]').forEach((modalEl) => setupExitIntentForModal(modalEl, canShowModal));
};

const setupExitIntentForModal = (modalEl, canShowModal) => {
  const modalId = modalEl.id;
  if (!modalId) return;

  const sessionKey = `exitIntentShown:${modalId}`;
  if (SHOW_ONCE_PER_SESSION && localStorage.getItem(sessionKey)) return;

  const markShownThisSession = () => {
    localStorage.setItem(sessionKey, "1");
  };

  const removeExitIntentListeners = () => {
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

  const handleMouseOut = (e) => {
    const related = e.relatedTarget || e.toElement;
    if (related) return; // sigue dentro del documento
    if (e.clientY <= EXIT_TOP_THRESHOLD_PX) triggerModalOnce();
  };

  const handleMouseLeave = (e) => {
    if (!e.relatedTarget && !e.toElement) triggerModalOnce();
  };

  const handleVisibilityChange = () => {
    if (document.visibilityState === "hidden") triggerModalOnce();
  };

  const addExitIntentListeners = () => {
    document.addEventListener("mouseout", handleMouseOut, true);
    document.addEventListener("mouseleave", handleMouseLeave, true);
    document.addEventListener("visibilitychange", handleVisibilityChange, true);
  };

  addExitIntentListeners();
};

document.addEventListener("DOMContentLoaded", () => {
  initExitIntentCapture().catch((err) => console.error("Exit intent init error:", err));
});
