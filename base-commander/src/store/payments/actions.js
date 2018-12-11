import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setPayments = payments => {
  return {
    type: types.SET_PAYMENTS,
    payload: payments
  }
};
