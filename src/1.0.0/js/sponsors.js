"use strict";

document.addEventListener("click", (e) => {
  e = e || window.event;
  const target = e.target || e.srcElement;
  const slug = target.getAttribute("slug");

  if (target.hasAttribute("data-toggle") && target.getAttribute("data-toggle") == "emms__register-modal") {
    if (target.hasAttribute("data-target")) {
      sessionStorage.setItem("currentSlug", slug);
      const m_ID = target.getAttribute("data-target");
      document.getElementById(m_ID).classList.add("open");
      document.querySelector("body").style.overflow = "hidden";
      e.preventDefault();
    }
  }

  if ((target.hasAttribute("data-dismiss") && target.getAttribute("data-dismiss") == "emms__register-modal") || target.classList.contains("emms__register-modal")) {
    const modal = document.querySelector('[class="emms__register-modal open"]');
    modal.classList.remove("open");
    document.querySelector("body").style.overflow = "scroll";
    e.preventDefault();
  }
});
