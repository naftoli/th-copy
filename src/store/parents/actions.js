import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setParents = parents => {
  return {
    type: types.SET_PARENTS,
    payload: parents
  }
};

export const setChildren = children => {
  return {
    type: types.SET_CHILDREN,
    payload: children
  }
};
