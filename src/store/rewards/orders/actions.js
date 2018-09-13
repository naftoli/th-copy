import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setOrders = orders => {
  return {
    type: types.SET_ORDERS,
    payload: orders
  }
};

export const setStore = store => {
  return {
    type: types.SET_STORE,
    payload: store
  }
};
