import * as types from './types';

export const setModuleLoading = (module, loading) => {
  return {
    type: types.SET_MODULE_LOADING,
    payload: {module, loading}
  }
};
export const setModule = (module, data) => {
  return {
    type: types.SET_MODULE,
    payload: {module, data}
  }
};
