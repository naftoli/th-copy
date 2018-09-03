import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setPrizes = prizes => {
  return {
    type: types.SET_PRIZES,
    payload: prizes
  }
};
