export const STEPS = {
  IDENTIFICATION: "identification",
  CUSTOMER_DATA: "customer-data",
  PAYMENT: "payment",
};

export const STEP_ORDER = [STEPS.IDENTIFICATION, STEPS.CUSTOMER_DATA, STEPS.PAYMENT];

export function buildCustomerData(overrides = {}) {
  return {
    email: "",
    name: "",
    phone: "",
    profileExists: false,
    isFree: false,
    isVip: false,
    ...overrides,
  };
}

export function createCheckoutState(root) {
  return {
    origin: root?.dataset.origin || "checkout",
    successPath: root?.dataset.successPath || "/checkout-success",
    pricing: null,
    pricingLoading: false,
    pricingLoadAttempted: false,
    pricingStale: false,
    appliedCoupon: null,
    eprotectClient: null,
    eprotectReady: false,
    eprotectVisible: false,
    eprotectLoading: false,
    paymentStatusMessage: "",
    paymentStatusIsError: false,
    paymentInFlight: false,
    activePaymentId: null,
    customerProfile: null,
    customerProfileEmail: null,
    resolvedCouponCode: null,
    urlCouponCode: null,
    couponMode: "closed",
    lastPaymentOutcome: null,
    currentCustomerPayload: null,
    selectedTicketCode: null,
    availableTickets: [],
    ticketLoadError: "",
    pricingRequestId: 0,
    phoneControl: null,
    phoneInputReady: false,
    phoneInputFailed: false,
    customerMode: "email",
    currentStep: STEPS.IDENTIFICATION,
    customerData: buildCustomerData(),
  };
}

export function createPaymentId() {
  const cryptoApi = window.crypto;

  if (!cryptoApi) {
    return null;
  }

  if (typeof cryptoApi.randomUUID === "function") {
    return cryptoApi.randomUUID();
  }

  if (typeof cryptoApi.getRandomValues !== "function") {
    return null;
  }

  const bytes = new Uint8Array(16);
  cryptoApi.getRandomValues(bytes);

  bytes[6] = (bytes[6] & 0x0f) | 0x40;
  bytes[8] = (bytes[8] & 0x3f) | 0x80;

  const hex = Array.from(bytes, (byte) => byte.toString(16).padStart(2, "0"));

  return [hex.slice(0, 4).join(""), hex.slice(4, 6).join(""), hex.slice(6, 8).join(""), hex.slice(8, 10).join(""), hex.slice(10, 16).join("")].join("-");
}
