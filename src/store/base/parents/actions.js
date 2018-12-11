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

export const addChild = ( admin_id, user_id ) => {
  return {
    type: types.ADD_CHILD,
    payload: { admin_id, user_id }
  }
};

export const removeChild = ( admin_id, user_id ) => {
  return {
    type: types.REMOVE_CHILD,
    payload: { admin_id, user_id }
  }
};
