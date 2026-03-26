"use strict";

export const isQADomain = () => window.location.host === "qa.goemms.com" || window.location.host === "localhost";

export const createDownloadLink = (blob, fileName) => {
  const urlCreator = window.URL || window.webkitURL;
  const imageUrl = urlCreator.createObjectURL(blob);
  const tag = document.createElement("a");
  tag.href = imageUrl;
  tag.download = fileName;
  document.body.appendChild(tag);
  tag.click();
  document.body.removeChild(tag);
};
