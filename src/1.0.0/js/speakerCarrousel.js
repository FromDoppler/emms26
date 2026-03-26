const speakerCarousels = document.querySelectorAll(".speaker-carousel");
if (speakerCarousels) {
  // One flickity instance per carousel by day
  speakerCarousels.forEach((carousel) => {
    new Flickity(carousel, {
      cellAlign: "center",
      contain: true,
      wrapAround: true,
      draggable: true,
      pageDots: false,
      prevNextButtons: true,
      fade: true,
    });
  });
}
