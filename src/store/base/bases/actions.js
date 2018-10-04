import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setBases = bases => {
  return {
    type: types.SET_BASES,
    payload: bases
  }
};

export const updateBase = ( id, updates ) => {
  return {
    type: types.UPDATE_BASE,
    payload: { id, updates }
  }
};
