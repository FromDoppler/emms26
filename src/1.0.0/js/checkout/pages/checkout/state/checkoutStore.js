export function createCheckoutStore(initialState, reducer) {
  let state = initialState;
  const listeners = new Set();

  function getState() {
    return state;
  }

  function notify(action) {
    listeners.forEach((listener) => listener(state, action));
  }

  function dispatch(action) {
    state = reducer(state, action);
    notify(action);
    return action;
  }

  function setState(updater, type = "STATE_PATCHED") {
    const nextState = typeof updater === "function" ? updater(state) : updater;
    state = {
      ...state,
      ...nextState,
    };
    notify({ type });
    return state;
  }

  function subscribe(listener) {
    listeners.add(listener);
    return function unsubscribe() {
      listeners.delete(listener);
    };
  }

  return {
    dispatch,
    getState,
    setState,
    subscribe,
  };
}
