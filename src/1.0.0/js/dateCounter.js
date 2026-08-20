// Date Counter

const utcDate = "2026-09-29T14:00:00.000Z";
const targetDate = new Date(utcDate).getTime();

const counters = [...document.querySelectorAll(".emms-counter")]
  .map((counter) => ({
    d: counter.querySelector(".d"),
    h: counter.querySelector(".h"),
    m: counter.querySelector(".m"),
    s: counter.querySelector(".s"),
  }))
  .filter((units) => Object.values(units).every((el) => el));

const updateCounter = () => {
  const now = new Date().getTime();
  const timeLeft = Math.max((targetDate - now) / 1000, 0);
  const values = {
    d: Math.floor(timeLeft / 86400),
    h: Math.floor((timeLeft % 86400) / 3600),
    m: Math.floor((timeLeft % 3600) / 60),
    s: Math.floor(timeLeft % 60),
  };

  counters.forEach((units) => {
    Object.entries(values).forEach(([unit, time]) => {
      const [digit1, digit2] = String(time).padStart(2, "0").split("");
      units[unit].querySelector(".digit-1").textContent = digit1;
      units[unit].querySelector(".digit-2").textContent = digit2;
    });
  });
};

if (counters.length) {
  updateCounter();
  setInterval(updateCounter, 1000);
}
