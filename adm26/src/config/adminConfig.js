export const ADMIN_BASE_PATH = window.ADM_CONFIG.basePath;

export const getEventId = (eventKey) => window.ADM_CONFIG.eventIds[eventKey] ?? eventKey;
