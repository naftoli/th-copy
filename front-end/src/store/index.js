import { configureStore } from '@reduxjs/toolkit';
import { types } from './login/actions';
import home from 'store/home/reducer';
import login from 'store/login/reducer';
import payments from 'store/payments/reducer';

// nested reducers
import base from './base';
import rewards from './rewards';
import missions from './missions';

// Root reducer function handling reset logic
const combinedReducer = {
  // nested
  rewards, base, missions,
  // flat
  payments, login, home,
};

const rootReducer = (state, action) => {
  // reset the state on logout
  if (action.type === types.LOGOUT) {
    state = undefined;
  }
  // reset all non-login state when login is changed
  if (action.type === types.CHANGE_LOGIN) {
    // Preserve login state, reset others
    // Note: This logic assumes 'login' is a top-level key.
    // Ideally we should reconstruct the state object properly or handle this in slices.
    // For now, replicating legacy logic:
    return appReducer({ login: state.login }, action);
  }
  return appReducer(state, action);
}

// We need to use combineReducers manually if we want to wrap it for rootReducer reset logic
// But configureStore accepts a reducer object or function.
import { combineReducers } from 'redux';
const appReducer = combineReducers(combinedReducer);

export default configureStore({
  reducer: rootReducer,
  middleware: (getDefaultMiddleware) => getDefaultMiddleware({
    immutableCheck: false,
    serializableCheck: false,
  }),
  devTools: import.meta.env.MODE !== 'production',
});

