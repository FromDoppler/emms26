let currentModalId = null;

export const openModal = (id, props = {}) => {
  return new Promise((resolve) => {
    if (currentModalId) {
      return resolve({ reason: "ignored" });
    }

    const delay = props.delay || 0;

    setTimeout(() => {
      _internalOpenModal(id, props).then((res) => {
        currentModalId = null;
        resolve(res);
      });
    }, delay);
  });
};

const _internalOpenModal = (id, props = {}) => {
  return new Promise((resolve) => {
    const overlay = document.getElementById(id);
    if (!overlay) return resolve({ reason: "missing" });

    const body = document.body;
    let done = false;

    const cleanup = () => {
      overlay.removeEventListener("click", onOverlayClick);
      document.removeEventListener("keydown", onEsc);
      observer.disconnect();
      body.classList.remove("modal-open");
      currentModalId = null;
    };

    const finish = (reason) => {
      if (done) return;
      done = true;
      cleanup();
      if (reason !== "external") overlay.setAttribute("aria-hidden", "true");
      resolve({ reason });
    };

    const onOverlayClick = (e) => {
      if (e.target === overlay || e.target.closest("[data-modal-close]")) {
        finish("close");
      }
    };

    const onEsc = (e) => {
      if (e.key === "Escape") finish("esc");
    };

    const observer = new MutationObserver(() => {
      if (overlay.getAttribute("aria-hidden") === "true") finish("external");
    });

    overlay.setAttribute("aria-hidden", "false");
    body.classList.add("modal-open");
    currentModalId = id;

    if (typeof props.onOpened === "function") {
      props.onOpened();
    }

    overlay.addEventListener("click", onOverlayClick);
    document.addEventListener("keydown", onEsc);
    observer.observe(overlay, { attributes: true, attributeFilter: ["aria-hidden"] });
  });
};

export const closeModal = (id) => {
  const overlay = document.getElementById(id);
  if (!overlay) return false;
  overlay.setAttribute("aria-hidden", "true");
  return true;
};
