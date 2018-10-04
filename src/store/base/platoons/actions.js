import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setPlatoons = platoons => {
  return {
    type: types.SET_PLATOONS,
    payload: platoons
  }
};

export const updatePlatoon = ( id, updates ) => {
  return {
    type: types.UPDATE_PLATOON,
    payload: { id, updates }
  }
};

export const deletePlatoon = id => {
  return {
    type: types.DELETE_PLATOON,
    payload: id
  }
};
