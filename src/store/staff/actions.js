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

export const removePosition = ( admin_id, auth, id ) => {
  return {
    type: types.REMOVE_POSITION,
    payload: { admin_id, auth, id }
  }
};
