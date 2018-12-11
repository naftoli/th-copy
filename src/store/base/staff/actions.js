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

export const updateStaff = ( admin_id, updates ) => {
  return {
    type: types.UPDATE_STAFF,
    payload: { admin_id, updates }
  }
};

export const removeAuth = ( auth ) => {
  return {
    type: types.REMOVE_AUTH,
    payload: auth
  }
};

export const createAuth = ( auth ) => {
  return {
    type: types.CREATE_AUTH,
    payload: auth
  }
};
