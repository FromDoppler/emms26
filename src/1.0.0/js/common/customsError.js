"use strict";

const customError = (customMessage, { name, message = "undefined", stack = "undefined" }) => {
  const errorInfo = {
    errorName: name,
    errorMessage: message,
    errorStack: stack,
  };
  throw {
    "Trowed in": customMessage,
    ...errorInfo,
    error: new Error(),
  };
};

export { customError };
