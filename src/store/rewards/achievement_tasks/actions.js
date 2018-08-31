import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setTasks = tasks => {
  return {
    type: types.SET_TASKS,
    payload: tasks
  }
};

export const addTask = task => {
  return {
    type: types.ADD_TASK,
    payload: task
  }
};

export const updateTask = task => {
  return {
    type: types.UPDATE_TASK,
    payload: task
  }
};
