import { setCouponStatusMessage, setStatusMessage } from "./checkoutComponents.js";
import { hasFreshPricing, isVipMode } from "../state/checkoutSelectors.js";

const COUPON_CODE_MAX_LENGTH = 18;

function normalizeCouponDraft(value) {
  return String(value || "")
    .toUpperCase()
    .slice(0, COUPON_CODE_MAX_LENGTH);
}

function syncCouponDraftInput(view) {
  const normalizedCouponCode = normalizeCouponDraft(view.couponInput.value);

  if (view.couponInput.value !== normalizedCouponCode) {
    view.couponInput.value = normalizedCouponCode;
  }

  return normalizedCouponCode.trim();
}

function clearCouponFeedback(view) {
  setCouponStatusMessage(view.couponStatus, "");
}

function formatCurrencyAmount(currency, amount, options = {}) {
  const prefix = options.negative ? "-" : "";
  return `${prefix}${currency} ${Number(Math.abs(amount || 0)).toFixed(2)}`;
}

function resolveTicketPrice(ticket) {
  if (!ticket) {
    return "";
  }

  if (ticket.priceLabel) {
    return ticket.priceLabel;
  }

  if (ticket.price !== undefined && ticket.currency) {
    return formatCurrencyAmount(ticket.currency, ticket.price);
  }

  return "";
}

function renderTicketPlaceholder(view, label, status = "", isError = false) {
  view.ticketSelect.innerHTML = "";
  const option = document.createElement("option");
  option.value = "";
  option.textContent = label;
  view.ticketSelect.appendChild(option);
  view.ticketSelect.disabled = true;
  setStatusMessage(view.ticketStatus, status, isError);
}

function renderTicketOptions(view, state, availableTickets = [], selectedTicket = null) {
  const tickets = Array.isArray(availableTickets) ? availableTickets : [];

  view.ticketSelect.innerHTML = "";

  if (tickets.length === 0) {
    renderTicketPlaceholder(view, "Sin accesos disponibles", "No hay tickets disponibles.", true);
    return;
  }

  if (tickets.length > 1) {
    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = "Seleccioná tu acceso";
    view.ticketSelect.appendChild(placeholder);
  }

  tickets.forEach((ticket) => {
    const option = document.createElement("option");
    option.value = ticket.code || "";
    option.textContent = [ticket.name || ticket.code || "Acceso", resolveTicketPrice(ticket)].filter(Boolean).join(" · ");
    view.ticketSelect.appendChild(option);
  });

  const requestedCode = selectedTicket?.code || state.selectedTicketCode || "";
  const selectedCode = tickets.some((ticket) => ticket.code === requestedCode) ? requestedCode : tickets.length === 1 ? tickets[0].code : "";

  view.ticketSelect.value = selectedCode;
  view.ticketSelect.disabled = tickets.length <= 1;
  setStatusMessage(view.ticketStatus, selectedCode ? "" : state.ticketLoadError || "Seleccioná tu acceso.");
}

function getPricingTickets(pricing = {}) {
  if (Array.isArray(pricing.availableTickets) && pricing.availableTickets.length > 0) {
    return pricing.availableTickets;
  }

  if (pricing.ticket) {
    return [pricing.ticket];
  }

  return [];
}

function findSelectedTicket(state) {
  const tickets = Array.isArray(state.availableTickets) ? state.availableTickets : [];
  const selectedCode = state.selectedTicketCode || "";

  return tickets.find((ticket) => ticket.code === selectedCode) || (tickets.length === 1 ? tickets[0] : null);
}

function renderTicketEstimate(view, state) {
  const ticket = findSelectedTicket(state);

  if (!ticket) {
    view.summaryTicket.textContent = "-";
    view.summaryTicket.classList.remove("emms__checkout__summary-ticket-badge");
    view.summaryAmount.textContent = "USD 0.00";
    view.summaryDiscountRow.hidden = true;
    view.summaryTotal.textContent = "USD 0.00";
    return;
  }

  const currency = ticket.currency || "USD";
  const amount = Number(ticket.price || 0);
  const ticketName = ticket.name || ticket.code || "VIP";

  view.summaryTicket.textContent = ticketName;
  view.summaryTicket.classList.toggle("emms__checkout__summary-ticket-badge", Boolean(ticketName));
  view.summaryAmount.textContent = formatCurrencyAmount(currency, amount);
  view.summaryDiscountRow.hidden = true;
  view.summaryTotal.textContent = formatCurrencyAmount(currency, amount);
}

function renderPricingSummary(view, pricing) {
  const currency = pricing?.currency || pricing?.ticket?.currency || "USD";
  const subtotal = Number(pricing?.amount ?? pricing?.ticket?.price ?? 0);
  const total = Number(pricing?.finalAmount ?? subtotal);
  const discountAmount = Number(pricing?.discount?.amount || Math.max(subtotal - total, 0));
  const ticketName = pricing?.ticket?.name || pricing?.ticket?.code || "VIP";

  view.summaryTicket.textContent = ticketName;
  view.summaryTicket.classList.toggle("emms__checkout__summary-ticket-badge", Boolean(ticketName));
  view.summaryAmount.textContent = formatCurrencyAmount(currency, subtotal);
  view.summaryTotal.textContent = formatCurrencyAmount(currency, total);

  view.summaryDiscountRow.hidden = discountAmount <= 0;
  view.summaryDiscount.textContent = formatCurrencyAmount(currency, discountAmount, { negative: true });
}

function renderCouponControls(state, view) {
  const isVip = isVipMode(state);
  const hasAppliedCoupon = Boolean(state.appliedCoupon);

  view.couponSection.dataset.couponMode = state.couponMode;
  view.couponToggleButton.hidden = isVip || hasAppliedCoupon;
  view.couponEditor.hidden = state.couponMode !== "editing" || isVip || hasAppliedCoupon;
  view.couponApplied.hidden = !hasAppliedCoupon || isVip;

  const toggleLabel = view.couponToggleButton.querySelector("strong");
  if (toggleLabel) {
    toggleLabel.textContent = state.couponMode === "editing" ? "Cancelar" : "Agregar";
  }
  view.couponToggleButton.setAttribute("aria-expanded", state.couponMode === "editing" ? "true" : "false");

  view.applyCouponButton.disabled = false;
  view.couponInput.disabled = false;

  if (state.urlCouponCode && !view.couponInput.value && !hasAppliedCoupon) {
    view.couponInput.value = state.urlCouponCode;
  }

  view.couponAppliedCode.textContent = state.appliedCoupon || "-";
  view.couponAppliedSource.hidden = !state.urlCouponCode || state.appliedCoupon !== state.urlCouponCode;
  view.removeCouponButton.hidden = !hasAppliedCoupon;
}

export function renderSummaryView(state, view) {
  const isVip = isVipMode(state);

  view.summaryPanel.dataset.summaryMode = isVip ? "vip" : "checkout";
  view.summaryVipNotice.hidden = !isVip;
  view.summaryAccess.hidden = isVip;
  view.summaryPricing.hidden = isVip;
  view.secureNote.hidden = isVip;
  view.summarySecondaryAction.hidden = !isVip;

  renderCouponControls(state, view);

  if (hasFreshPricing(state)) {
    renderTicketOptions(view, state, getPricingTickets(state.pricing), state.pricing.ticket || null);
    renderPricingSummary(view, state.pricing);
  } else if (!isVip) {
    if (state.availableTickets && state.availableTickets.length > 0) {
      renderTicketOptions(view, state, state.availableTickets, null);
      renderTicketEstimate(view, state);
    } else {
      if (state.pricingLoading || !state.pricingLoadAttempted) {
        renderTicketPlaceholder(view, "Cargando...", "Buscando accesos disponibles.");
      } else if (state.ticketLoadError) {
        renderTicketPlaceholder(view, "No se pudo cargar el acceso", state.ticketLoadError, true);
      } else {
        renderTicketPlaceholder(view, "No se pudo cargar el acceso", "No pudimos cargar el acceso. Intentá nuevamente en unos segundos.", true);
      }

      view.summaryTicket.textContent = "-";
      view.summaryTicket.classList.remove("emms__checkout__summary-ticket-badge");
      view.summaryAmount.textContent = "USD 0.00";
      view.summaryDiscountRow.hidden = true;
      view.summaryTotal.textContent = "USD 0.00";
    }
  }
}

export function createSummaryComponent(context) {
  const { store, view } = context;

  function handleTicketChange() {
    store.dispatch({
      type: "TICKET_CHANGED",
      ticketCode: view.ticketSelect.value || null,
    });

    clearCouponFeedback(view);
    return true;
  }

  function toggleCouponEditor() {
    const nextMode = store.getState().couponMode === "editing" ? "closed" : "editing";
    store.dispatch({
      type: "COUPON_MODE_CHANGED",
      mode: nextMode,
    });

    clearCouponFeedback(view);

    if (nextMode === "editing") {
      view.couponInput.value = "";
      view.couponInput.focus();
    }
  }

  function handleCouponInput() {
    syncCouponDraftInput(view);
    clearCouponFeedback(view);
  }

  function prepareCouponApply() {
    store.dispatch({ type: "PRICING_STALE" });
    return true;
  }

  function readCouponDraft() {
    return syncCouponDraftInput(view);
  }

  function discardFailedCouponAttempt() {
    const failedCouponCode = readCouponDraft();

    view.couponInput.value = "";
    store.dispatch({ type: "COUPON_ATTEMPT_REJECTED" });
    return failedCouponCode;
  }

  function clearCouponDraft() {
    view.couponInput.value = "";
    clearCouponFeedback(view);

    if (store.getState().couponMode === "editing") {
      store.dispatch({
        type: "COUPON_MODE_CHANGED",
        mode: "closed",
      });
    }

    return true;
  }

  function removeCoupon() {
    view.couponInput.value = "";
    store.dispatch({ type: "COUPON_REMOVED" });
    return true;
  }

  return {
    render(state = store.getState()) {
      renderSummaryView(state, view);
    },
    clearCouponDraft,
    clearCouponFeedback() {
      clearCouponFeedback(view);
    },
    discardFailedCouponAttempt,
    handleCouponInput,
    handleTicketChange,
    prepareCouponApply,
    readCouponDraft,
    removeCoupon,
    toggleCouponEditor,
  };
}
