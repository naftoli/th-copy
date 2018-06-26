import * as types from './types';
import uuid from 'uuid/v1';

export const addError = message => {
  return {
    type: types.ADD_ERROR,
    payload: {
      id: uuid(),
      message: message
    }
  }
};

export const clearError = index => {
  return {
    type: types.CLEAR_ERROR,
    payload: index
  }
};