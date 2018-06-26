import * as types from './types';
import uuid from 'uuid/v1';
import { Intent } from "@blueprintjs/core";

export const addError = message => {
  return {
    type: types.ADD_ERROR,
    payload: {
      id: uuid(),
      message: message,
      intent: Intent.DANGER
    }
  }
};

export const clearError = index => {
  return {
    type: types.CLEAR_ERROR,
    payload: index
  }
};