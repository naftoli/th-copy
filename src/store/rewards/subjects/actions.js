import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setSubjects = tasks => {
  return {
    type: types.SET_SUBJECTS,
    payload: tasks
  }
};
