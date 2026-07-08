const COUPON_ERROR_MESSAGES = {
  coupon_invalid: "El código ingresado no es válido. Revisalo o probá con otro.",
  coupon_not_found: "No encontramos ese código. Revisalo o probá con otro.",
  coupon_expired: "Ese código ya venció.",
  coupon_inactive: "Ese código no está disponible para este evento.",
  coupon_usage_exceeded: "Ese código ya alcanzó su límite de usos.",
  coupon_already_used: "Ese código ya fue utilizado.",
};

const CHECKOUT_ERROR_MESSAGES = {
  pricing_timeout: "No pudimos calcular el precio. Intentá nuevamente.",
  pricing_failed: "No pudimos calcular el precio. Intentá nuevamente.",
};

function formatCouponCodeForMessage(couponCode) {
  return String(couponCode || "")
    .trim()
    .toUpperCase()
    .slice(0, 18);
}

export function isCouponError(error) {
  return String(error || "").startsWith("coupon_");
}

export function resolveCouponErrorMessage(error, couponCode = "") {
  const code = formatCouponCodeForMessage(couponCode);

  if (!code) {
    return COUPON_ERROR_MESSAGES[String(error || "")] || "No pudimos aplicar el cupón. Revisá el código o probá nuevamente.";
  }

  if (error === "coupon_expired") {
    return `Cupón vencido\n${code}`;
  }

  if (error === "coupon_inactive") {
    return `Cupón no disponible\n${code}`;
  }

  if (error === "coupon_usage_exceeded") {
    return `Límite de usos alcanzado\n${code}`;
  }

  if (error === "coupon_already_used") {
    return `Cupón ya utilizado\n${code}`;
  }

  return `Cupón no válido\n${code}`;
}

export function resolveCheckoutErrorMessage(error) {
  return CHECKOUT_ERROR_MESSAGES[String(error || "")] || "No pudimos actualizar el checkout. Intentá nuevamente.";
}
