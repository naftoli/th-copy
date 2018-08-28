import * as types from './types';

export const setRegistration = data => {
  return {
    type: types.SET_REGISTRATION,
    payload: data
  }
};

export const setBirthdays = birthdays => {
  return {
    type: types.SET_BIRTHDAYS,
    payload: birthdays
  }
};
