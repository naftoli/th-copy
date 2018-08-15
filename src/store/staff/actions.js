import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setStaff = staff => {
  return {
    type: types.SET_STAFF,
    payload: staff
  }
};
