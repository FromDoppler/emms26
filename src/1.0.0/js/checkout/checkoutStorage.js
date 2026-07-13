export function persistDplridFromCustomerEmail(email) {
  const normalizedEmail = String(email || "")
    .trim()
    .toLowerCase();

  if (!normalizedEmail) {
    return;
  }

  safeLocalStorageSet("dplrid", toHex(normalizedEmail));
}

export function getCustomerIdentity() {
  const urlParams = new URLSearchParams(window.location.search);
  const localDplrid = safeLocalStorageGet("dplrid");
  const queryDplrid = urlParams.get("dplrid");
  const hex = queryDplrid || localDplrid;
  const decoded = hexToString(hex);

  if (!decoded) {
    return { email: "", source: "none" };
  }

  try {
    return {
      email: JSON.parse(decoded).userEmail || decoded,
      source: queryDplrid ? "query" : localDplrid ? "localStorage" : "none",
    };
  } catch (error) {
    return {
      email: decoded,
      source: queryDplrid ? "query" : localDplrid ? "localStorage" : "none",
    };
  }
}

function toHex(value) {
  const normalizedValue = String(value || "");
  let result = "";

  for (let i = 0; i < normalizedValue.length; i += 1) {
    result += normalizedValue.charCodeAt(i).toString(16).padStart(2, "0");
  }

  return result;
}

function safeLocalStorageGet(key) {
  try {
    return localStorage.getItem(key);
  } catch (error) {
    return null;
  }
}

function safeLocalStorageSet(key, value) {
  try {
    localStorage.setItem(key, value);
    return true;
  } catch (error) {
    return false;
  }
}

function hexToString(hex) {
  try {
    if (!hex) {
      return null;
    }

    let str = "";
    for (let i = 0; i < hex.length; i += 2) {
      str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
    }
    return str;
  } catch (error) {
    return null;
  }
}

export function updateCheckoutEvents(currentEvent = window.APP?.EVENTS?.CURRENT) {
  try {
    const { vipId, freeId } = currentEvent || {};
    const storedEvents = JSON.parse(localStorage.getItem("events") || "[]");
    const existingEvents = Array.isArray(storedEvents) ? storedEvents : [];
    const updatedEvents = new Set(existingEvents);

    if (vipId) updatedEvents.add(vipId);
    if (freeId) updatedEvents.add(freeId);

    localStorage.setItem("events", JSON.stringify([...updatedEvents]));
  } catch (error) {
    // Best effort only: checkout success must not fail because localStorage is unavailable or corrupted.
  }
}
