"use strict";

import { getEncodeURLEmail } from "./index.js";

const isUserLogged = () => {
  const loggedLocalStorage = getUserLoggedFromLocalStorage() === false ? false : true;
  const loggedInUrl = getEncodeURLEmail() === false ? false : true;

  return loggedLocalStorage || loggedInUrl;
};

const getUserLoggedFromLocalStorage = () => {
  return localStorage.getItem("dplrid") === null ? false : localStorage.getItem("dplrid");
};

export { isUserLogged };
