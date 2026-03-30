// Date Counter

const utcDate = "2025-04-28T14:00:00.000Z";
const targetDate = new Date(utcDate).getTime();

const containers = {
  d: document.querySelector(".d"),
  h: document.querySelector(".h"),
  m: document.querySelector(".m"),
  s: document.querySelector(".s"),
};

const updateCounter = () => {
  const now = new Date().getTime();
  const timeLeft = Math.max((targetDate - now) / 1000, 0);
  const days = Math.floor(timeLeft / 86400);
  const hours = Math.floor((timeLeft % 86400) / 3600);
  const minutes = Math.floor((timeLeft % 3600) / 60);
  const seconds = Math.floor(timeLeft % 60);

  displayTime("d", days);
  displayTime("h", hours);
  displayTime("m", minutes);
  displayTime("s", seconds);
};

const displayTime = (unit, time) => {
  const [digit1, digit2] = String(time).padStart(2, "0").split("");
  containers[unit].querySelector(".digit-1").textContent = digit1;
  containers[unit].querySelector(".digit-2").textContent = digit2;
};

if (Object.values(containers).every((el) => el)) {
  updateCounter();
  setInterval(updateCounter, 1000);
}
