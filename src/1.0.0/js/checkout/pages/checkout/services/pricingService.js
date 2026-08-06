import { calculatePayment } from "./checkoutService.js";
import { isCouponError } from "../checkoutMessages.js";

function isObject(value) {
  return Boolean(value && typeof value === "object" && !Array.isArray(value));
}

function isVipCustomerProfile(customerProfile) {
  return Boolean(customerProfile && customerProfile.exists && customerProfile.isVip);
}

function normalizeCalculatePayload(payload = {}) {
  if (!isObject(payload)) {
    return {
      success: false,
      error: "invalid_calculate_response",
      availableTickets: [],
    };
  }

  return {
    ...payload,
    availableTickets: Array.isArray(payload.availableTickets) ? payload.availableTickets : [],
  };
}

function dispatchPricingFailed(store, ticketLoadError = "No se pudieron cargar los tickets.") {
  store.dispatch({
    type: "PRICING_FAILED",
    ticketLoadError,
  });
}

export function createPricingService({ store, buildPayload }) {
  async function calculate({ couponCode = null } = {}) {
    const requestedId = store.getState().pricingRequestId + 1;
    store.dispatch({ type: "PRICING_REQUESTED" });

    try {
      const state = store.getState();
      const requestPayload = buildPayload(state, { couponCode });
      const { response, payload: rawPayload } = await calculatePayment(requestPayload);

      if (store.getState().pricingRequestId !== requestedId) {
        return { ok: false, stale: true };
      }

      const payload = normalizeCalculatePayload(rawPayload);
      const customerProfile = Object.prototype.hasOwnProperty.call(payload, "customerProfile") ? payload.customerProfile || null : undefined;

      if (isVipCustomerProfile(customerProfile)) {
        return {
          ok: true,
          customerProfile,
          skipPricingForVip: true,
        };
      }

      if (payload.error === "ticket_required" || payload.error === "ticket_unavailable") {
        const availableTickets = Array.isArray(payload.availableTickets) ? payload.availableTickets : [];

        if (availableTickets.length === 0) {
          dispatchPricingFailed(store, "No hay tickets disponibles.");
          return {
            ok: false,
            error: payload.error || "ticket_unavailable",
            customerProfile,
          };
        }

        const currentSelectedTicketCode = store.getState().selectedTicketCode || null;
        const singleTicketCode = availableTickets.length === 1 ? availableTickets[0].code : null;
        const selectedTicketCode = payload.ticket?.code || singleTicketCode || currentSelectedTicketCode;

        store.dispatch({
          type: "TICKETS_AVAILABLE",
          availableTickets,
          selectedTicketCode,
        });

        return {
          ok: false,
          customerProfile,
        };
      }

      if (!response.ok || payload.success === false) {
        if (isCouponError(payload.error)) {
          return {
            ok: false,
            error: payload.error,
            customerProfile,
          };
        }

        if (Array.isArray(payload.availableTickets) && payload.availableTickets.length > 0) {
          store.dispatch({
            type: "TICKETS_AVAILABLE",
            availableTickets: payload.availableTickets,
            selectedTicketCode: payload.ticket?.code || store.getState().selectedTicketCode || null,
          });
        } else {
          dispatchPricingFailed(store);
        }

        return {
          ok: false,
          error: payload.error || "pricing_failed",
          customerProfile,
        };
      }

      store.dispatch({
        type: "PRICING_LOADED",
        pricing: payload,
      });

      return {
        ok: true,
        customerProfile,
      };
    } catch (error) {
      if (store.getState().pricingRequestId === requestedId) {
        dispatchPricingFailed(store);
      }

      return {
        ok: false,
        error: error?.name === "AbortError" ? "pricing_timeout" : "pricing_failed",
      };
    }
  }

  return { calculate };
}
