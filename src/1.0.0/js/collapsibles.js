const collapsiblesQuestionsListeners = () => {
  const accItem = document.querySelectorAll(".emms__frequentquestions__list__item");
  const accHD = document.querySelectorAll(".emms__frequentquestions__list__item__head");

  if (!accItem.length || !accHD.length) {
    return;
  }

  const toggleQuestionItem = (itemCta) => {
    const currentItem = itemCta.parentElement;
    const shouldOpen = currentItem.classList.contains("close") || !currentItem.classList.contains("open");

    accItem.forEach((item) => {
      item.classList.remove("open");
      item.classList.add("close");
    });

    if (shouldOpen) {
      currentItem.classList.remove("close");
      currentItem.classList.add("open");
    }
  };

  accHD.forEach((itemCta) => {
    if (itemCta.dataset.collapsibleBound === "true") {
      return;
    }

    itemCta.dataset.collapsibleBound = "true";
    itemCta.addEventListener("click", () => {
      toggleQuestionItem(itemCta);
    });
  });
};

const collapsibleLegalListeners = () => {
  const legalBtn = document.getElementById("legalBtn");

  if (!legalBtn || legalBtn.dataset.collapsibleBound === "true") {
    return;
  }

  legalBtn.dataset.collapsibleBound = "true";
  legalBtn.addEventListener("click", () => {
    legalBtn.parentNode.classList.toggle("open");
  });
};

const collapsiblesListListeners = () => {
  const listItems = document.querySelectorAll(".emms__collapse__list");
  const listBtns = document.querySelectorAll(".emms__collapse-btn");

  if (!listItems.length || !listBtns.length) {
    return;
  }

  const toggleItem = (btn) => {
    const currentItem = btn.parentElement;
    const shouldOpen = currentItem.classList.contains("close") || !currentItem.classList.contains("open");

    listItems.forEach((item) => {
      item.classList.remove("open");
      item.classList.add("close");
    });

    if (shouldOpen) {
      currentItem.classList.remove("close");
      currentItem.classList.add("open");
    }
  };

  listBtns.forEach((btn) => {
    if (btn.dataset.collapsibleBound === "true") {
      return;
    }

    btn.dataset.collapsibleBound = "true";
    btn.addEventListener("click", () => {
      toggleItem(btn);
    });
  });
};

const initCollapsibles = () => {
  collapsiblesQuestionsListeners();
  collapsibleLegalListeners();
  collapsiblesListListeners();
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initCollapsibles);
} else {
  initCollapsibles();
}
