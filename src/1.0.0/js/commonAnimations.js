// Header Animation

let scrollpos = window.scrollY;
const header = document.querySelector(".emms__header");
const header_height = header.offsetHeight;
const add_class_on_scroll = () => header.classList.add("emms__header-scroll");
const remove_class_on_scroll = () => header.classList.remove("emms__header-scroll");

window.addEventListener("scroll", function () {
  scrollpos = window.scrollY;
  if (scrollpos >= 40) {
    add_class_on_scroll();
  } else {
    remove_class_on_scroll();
  }
});

// Animation viewport scroll

(function () {
  var elements;
  var elements2;
  var windowHeight;

  function init() {
    elements = document.querySelectorAll(".emms__fade-in");
    elements2 = document.querySelectorAll(".emms__fade-top");
    windowHeight = window.innerHeight;
  }

  function checkPosition() {
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      var positionFromTop = elements[i].getBoundingClientRect().top;

      if (window.scrollY == 0) {
        element.classList.add("emms__fade-in-animation");
        element.classList.remove("emms__fade-in");
      } else if (positionFromTop - windowHeight <= 0) {
        element.classList.add("emms__fade-in-animation");
        element.classList.remove("emms__fade-in");
      } else {
        element.classList.remove("emms__fade-in-animation");
        element.classList.add("emms__fade-in");
      }
    }
  }

  function checkPosition2() {
    for (var i = 0; i < elements2.length; i++) {
      var element2 = elements2[i];
      var positionFromTop = elements2[i].getBoundingClientRect().top;

      if (positionFromTop - windowHeight <= 0) {
        element2.classList.add("emms__fade-top-animation");
        element2.classList.remove("emms__fade-top");
      } else {
        element2.classList.remove("emms__fade-top-animation");
        element2.classList.add("emms__fade-top");
      }
    }
  }

  window.addEventListener("scroll", checkPosition, checkPosition2);
  window.addEventListener("resize", init);

  init();
  checkPosition();
  checkPosition2();
})();

// Flickity Carousel Home
const homeCarousel = document.querySelector(".main-carousel");
if (homeCarousel) {
  const flktyHome = new Flickity(homeCarousel, {
    cellAlign: "center",
    contain: true,
    prevNextButtons: true,
    fade: true,
    wrapAround: true,
  });
}

// Flickity Carousel Academy Banner
const academyCarousel = document.querySelector(".academy-carousel");
if (academyCarousel) {
  const flktyAcademy = new Flickity(academyCarousel, {
    groupCells: 1,
    cellAlign: "left",
    contain: true,
    prevNextButtons: true,
    fade: true,
    wrapAround: true,
    pageDots: false,
  });
}

// Mobile nav

const heading = document.getElementById("nav-mb");
const btn = document.getElementById("btn-burger");
const hellobar = document.querySelector(".hellobar");
if (btn != undefined && btn != null) {
  btn.addEventListener("click", (e) => {
    hellobar.classList.toggle("hide");
    heading.classList.toggle("emms__header__nav--hidden");
    btn.classList.toggle("emms__header__nav--mb--active");
  });
}

// Share social network

const shareList = document.getElementById("list-share");
const share = document.getElementById("btn-share");
if (share != undefined && share != null) {
  share.addEventListener("click", (e) => {
    shareList.classList.toggle("emms__share__list--active");
    share.classList.toggle("emms__share--active");
  });
}
