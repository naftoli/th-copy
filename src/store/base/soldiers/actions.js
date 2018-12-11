import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const addSoldier = soldier => {
  return {
    type: types.ADD_SOLDIER,
    payload: soldier
  }
}

export const setSoldiers = soldiers => {
  return {
    type: types.SET_SOLDIERS,
    payload: soldiers
  }
};

export const setRegistrationSoldiers = soldiers => {
  return {
    type: types.SET_REGISTRATION_SOLDIERS,
    payload: soldiers
  }
};

export const updateSoldier = ( id, updates ) => {
  return {
    type: types.UPDATE_SOLDIER,
    payload: { id, updates }
  }
};

export const deleteSoldier = ( id ) => {
  return {
    type: types.DELETE_SOLDIER,
    payload: id
  }
};
