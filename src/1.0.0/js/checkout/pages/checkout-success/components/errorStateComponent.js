function byId(id) {
  return document.getElementById(id);
}

function setElementTextById(id, value) {
  const element = byId(id);
  if (element) {
    element.textContent = value || "";
  }
}

function setElementHiddenById(id, hidden) {
  const element = byId(id);
  if (element) {
    element.hidden = hidden;
  }
}

export function renderErrorState(message, title = "No pudimos validar la compra") {
  setElementHiddenById("success", true);
  setElementHiddenById("error-state", false);
  setElementTextById("error-title", title);
  setElementTextById("error-message", message || "No se pudo consultar el pago.");
}
