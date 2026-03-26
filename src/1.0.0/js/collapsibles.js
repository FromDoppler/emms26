document.addEventListener("DOMContentLoaded", () => {
  // Collapsible Questions
  const collapsiblesQuestionsListeners = () => {
    const accItem = document.querySelectorAll(".emms__frequentquestions__list__item");
    const accHD = document.querySelectorAll(".emms__frequentquestions__list__item__head");
    const toggleQuestionItem = (itemCta) => {
      const itemClass = itemCta.parentNode.className;
      accItem.forEach((item) => {
        item.className = "emms__frequentquestions__list__item close";
      });
      if (itemClass == "emms__frequentquestions__list__item close") {
        itemCta.parentNode.className = "emms__frequentquestions__list__item open";
      }
    };
    accHD.forEach((itemCta) => {
      itemCta.addEventListener("click", () => {
        toggleQuestionItem(itemCta);
      });
    });
  };

  // Collapsible Legal
  const collapsibleLegalListeners = () => {
    const legalBtn = document.getElementById("legalBtn");
    const toggleItemLegal = () => {
      legalBtn.parentNode.classList.toggle("open");
    };
    if (legalBtn)
      legalBtn.addEventListener("click", () => {
        toggleItemLegal();
      });
  };

  // Collapsible List
  const collapsiblesListListeners = () => {
    const listItems = document.querySelectorAll(".emms__collapse__list");
    const listBtns = document.querySelectorAll(".emms__collapse-btn");
    const toggleItem = (btn) => {
      const itemClass = btn.parentNode.className;
      listItems.forEach((item) => {
        item.className = "emms__collapse__list close";
      });
      if (itemClass == "emms__collapse__list close") {
        btn.parentNode.classList.toggle("open");
      }
    };

    listBtns.forEach((btn) =>
      btn.addEventListener("click", () => {
        toggleItem(btn);
      }),
    );
  };

  collapsiblesQuestionsListeners();
  collapsibleLegalListeners();
  collapsiblesListListeners();
});
