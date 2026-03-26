// Number counter animation

const boxNumber = document.querySelector("#boxNumber");

const observerBoxNumber = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      // Counter animation
      const counterAnim = (qSelector, start = 0, end, duration = 1000) => {
        const target = document.querySelector(qSelector);
        let startTimestamp = null;
        const step = (timestamp) => {
          if (!startTimestamp) startTimestamp = timestamp;
          const progress = Math.min((timestamp - startTimestamp) / duration, 1);
          target.innerText = Math.floor(progress * (end - start) + start);
          if (progress < 1) {
            window.requestAnimationFrame(step);
          }
        };
        window.requestAnimationFrame(step);
      };
      counterAnim("#count1", 0, 250000, 1000);
      counterAnim("#count2", 0, 15, 1200);
      counterAnim("#count3", 0, 10, 1400);
      counterAnim("#count4", 0, 150, 1600);
      // End number counter animation
    } else {
      false;
    }
  });
});
if (boxNumber) {
  observerBoxNumber.observe(boxNumber);
}

// Number counter animation LARGE

const boxNumberLarge = document.querySelector("#boxNumberLarge");

const observerBoxNumberLarge = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      // Counter animation
      const counterAnimL = (qSelector, start = 0, end, duration = 1000) => {
        const target = document.querySelector(qSelector);
        let startTimestamp = null;
        const step = (timestamp) => {
          if (!startTimestamp) startTimestamp = timestamp;
          const progress = Math.min((timestamp - startTimestamp) / duration, 1);
          target.innerText = Math.floor(progress * (end - start) + start);
          if (progress < 1) {
            window.requestAnimationFrame(step);
          }
        };
        window.requestAnimationFrame(step);
      };
      counterAnimL("#count1L", 0, 390, 1000);
      counterAnimL("#count2L", 0, 17, 1200);
      counterAnimL("#count3L", 0, 10, 1400);
      counterAnimL("#count4L", 0, 240, 1600);
      // End number counter animation
    } else {
      false;
    }
  });
});
if (boxNumberLarge) {
  observerBoxNumberLarge.observe(boxNumberLarge);
}
