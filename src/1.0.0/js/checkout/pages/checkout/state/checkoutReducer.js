import { buildCustomerData, STEPS } from "./checkoutState.js";

function resetPaymentState(state, overrides = {}) {
  return {
    ...state,
    eprotectClient: null,
    eprotectReady: false,
    eprotectVisible: false,
    eprotectLoading: false,
    paymentStatusMessage: "",
    paymentStatusIsError: false,
    lastPaymentOutcome: null,
    ...overrides,
  };
}

export function checkoutReducer(state, action = {}) {
  switch (action.type) {
    case "CHECKOUT_INITIALIZED":
      return {
        ...state,
        customerData: buildCustomerData({ email: action.email || "" }),
        urlCouponCode: action.couponCode || null,
        couponMode: "closed",
        availableTickets: [],
        pricingLoading: false,
        pricingLoadAttempted: false,
        ticketLoadError: "",
        currentStep: STEPS.IDENTIFICATION,
        customerMode: "email",
      };

    case "EMAIL_ACCEPTED":
      return resetPaymentState(state, {
        customerData: buildCustomerData({ email: action.email || "" }),
        customerProfile: null,
        customerProfileEmail: null,
        pricingStale: true,
      });

    case "CUSTOMER_PROFILE_APPLIED":
      return resetPaymentState(state, {
        customerProfile: action.profile || null,
        customerProfileEmail: action.email || state.customerData.email || null,
        customerData: buildCustomerData(action.customerData || {}),
        customerMode: action.mode,
        currentStep: action.currentStep || state.currentStep,
      });

    case "CUSTOMER_EDITING_STARTED":
      return {
        ...state,
        customerMode: "anonymous_editing",
        currentStep: STEPS.CUSTOMER_DATA,
      };

    case "IDENTIFICATION_EDITING_STARTED":
      return {
        ...state,
        customerMode: "email",
        currentStep: STEPS.IDENTIFICATION,
        paymentStatusMessage: "",
        paymentStatusIsError: false,
      };

    case "CUSTOMER_DATA_CHANGED":
      return {
        ...state,
        customerData: {
          ...state.customerData,
          ...action.customerData,
        },
        lastPaymentOutcome: null,
      };

    case "PHONE_READY":
      return {
        ...state,
        phoneControl: action.control || state.phoneControl,
        phoneInputReady: true,
        phoneInputFailed: false,
      };

    case "PHONE_FAILED":
      return {
        ...state,
        phoneControl: null,
        phoneInputReady: false,
        phoneInputFailed: true,
      };

    case "STEP_REQUESTED":
      return {
        ...state,
        currentStep: action.step || state.currentStep,
      };

    case "PRICING_REQUESTED":
      return {
        ...state,
        pricingRequestId: state.pricingRequestId + 1,
        pricingLoading: true,
        pricingLoadAttempted: true,
        ticketLoadError: "",
      };

    case "PRICING_STALE":
      return resetPaymentState(state, {
        pricingRequestId: state.pricingRequestId + 1,
        pricingStale: true,
      });

    case "PRICING_LOADED":
      return {
        ...state,
        pricing: action.pricing || null,
        pricingLoading: false,
        pricingLoadAttempted: true,
        pricingStale: false,
        availableTickets: action.pricing?.availableTickets || state.availableTickets || [],
        ticketLoadError: "",
        selectedTicketCode: action.pricing?.ticket?.code || state.selectedTicketCode,
        appliedCoupon: action.pricing?.discount ? action.pricing.discount.couponCode || null : null,
        resolvedCouponCode: action.pricing?.discount ? action.pricing.discount.couponCode || null : null,
        couponMode: action.pricing?.discount ? "applied" : state.couponMode === "applied" ? "closed" : state.couponMode,
      };

    case "PRICING_FAILED":
      return {
        ...state,
        pricingLoading: false,
        pricingLoadAttempted: true,
        pricingStale: true,
        ticketLoadError: action.ticketLoadError || state.ticketLoadError,
      };

    case "TICKETS_AVAILABLE": {
      const tickets = Array.isArray(action.availableTickets) ? action.availableTickets : [];
      const selectedTicketCode =
        action.selectedTicketCode && tickets.some((ticket) => ticket.code === action.selectedTicketCode)
          ? action.selectedTicketCode
          : tickets.length === 1
            ? tickets[0].code
            : state.selectedTicketCode && tickets.some((ticket) => ticket.code === state.selectedTicketCode)
              ? state.selectedTicketCode
              : null;

      return resetPaymentState(state, {
        pricing: null,
        pricingLoading: false,
        pricingLoadAttempted: true,
        availableTickets: tickets,
        selectedTicketCode,
        ticketLoadError: selectedTicketCode ? "" : "Seleccioná tu acceso.",
        pricingStale: true,
      });
    }

    case "PRICING_SKIPPED_FOR_VIP":
      return resetPaymentState(state, {
        pricing: null,
        pricingLoading: false,
        pricingLoadAttempted: true,
        pricingStale: false,
        availableTickets: [],
        selectedTicketCode: null,
        appliedCoupon: null,
        resolvedCouponCode: null,
        couponMode: "closed",
        ticketLoadError: "",
      });

    case "TICKET_CHANGED":
      return resetPaymentState(state, {
        selectedTicketCode: action.ticketCode || null,
        ticketLoadError: action.ticketCode ? "" : "Seleccioná tu acceso.",
        appliedCoupon: null,
        resolvedCouponCode: null,
        pricingStale: true,
      });

    case "COUPON_MODE_CHANGED":
      return {
        ...state,
        couponMode: action.mode || "closed",
      };

    case "COUPON_ATTEMPT_REJECTED":
      return resetPaymentState(state, {
        urlCouponCode: null,
        appliedCoupon: null,
        resolvedCouponCode: null,
        couponMode: "closed",
        pricingLoading: false,
        pricingStale: state.pricing ? false : state.pricingStale,
        ticketLoadError: state.availableTickets && state.availableTickets.length > 0 ? "" : state.ticketLoadError,
      });

    case "COUPON_REMOVED":
      return resetPaymentState(state, {
        urlCouponCode: null,
        appliedCoupon: null,
        resolvedCouponCode: null,
        couponMode: "closed",
        pricingStale: true,
      });

    case "EPROTECT_CLIENT_SET":
      return {
        ...state,
        eprotectClient: action.client || null,
        eprotectReady: Boolean(action.ready),
      };

    case "EPROTECT_READY_CHANGED":
      return {
        ...state,
        eprotectReady: Boolean(action.ready),
      };

    case "EPROTECT_VISIBILITY_CHANGED":
      return {
        ...state,
        eprotectVisible: Boolean(action.visible),
      };

    case "EPROTECT_LOADING_CHANGED":
      return {
        ...state,
        eprotectLoading: Boolean(action.loading),
      };

    case "PAYMENT_STATUS_CHANGED":
      return {
        ...state,
        paymentStatusMessage: action.message || "",
        paymentStatusIsError: Boolean(action.isError),
      };

    case "PAYMENT_IN_FLIGHT_CHANGED":
      return {
        ...state,
        paymentInFlight: Boolean(action.paymentInFlight),
        currentCustomerPayload: action.customerPayload === undefined ? state.currentCustomerPayload : action.customerPayload,
      };

    case "PAYMENT_ATTEMPT_CHANGED":
      return {
        ...state,
        activePaymentId: action.paymentId || null,
      };

    case "PAYMENT_OUTCOME_CHANGED":
      return {
        ...state,
        lastPaymentOutcome: action.outcome || null,
      };

    default:
      return state;
  }
}
