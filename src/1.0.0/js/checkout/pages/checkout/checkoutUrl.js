export function getUrlCouponCode() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get("couponCode") || urlParams.get("promotionCode") || "";
}

export function getUtmParams(origin = "checkout") {
  const urlParams = new URLSearchParams(window.location.search);

  return {
    utm_source: urlParams.get("utm_source") || "",
    utm_medium: urlParams.get("utm_medium") || "",
    utm_campaign: urlParams.get("utm_campaign") || "",
    utm_term: urlParams.get("utm_term") || "",
    utm_content: urlParams.get("utm_content") || "",
    origin: urlParams.get("origin") || origin,
  };
}

export function removeCouponCodeParamFromUrl() {
  const url = new URL(window.location.href);
  const hadCouponCode = url.searchParams.has("couponCode");
  const hadPromotionCode = url.searchParams.has("promotionCode");

  if (!hadCouponCode && !hadPromotionCode) {
    return;
  }

  url.searchParams.delete("couponCode");
  url.searchParams.delete("promotionCode");
  window.history.replaceState({}, "", url.toString());
}
