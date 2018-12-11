import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setCards = cards => {
  return {
    type: types.SET_CARDS,
    payload: cards
  }
};

export const setMiles = miles => {
  return {
    type: types.SET_MILES,
    payload: miles
  }
};
