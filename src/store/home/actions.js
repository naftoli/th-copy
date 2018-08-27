import * as types from './types';

export const setRegistration = data => {
  return {
    type: types.SET_REGISTRATION,
    payload: data
  }
};
