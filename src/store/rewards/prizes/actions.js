import * as types from './types';

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setPrizes = prizes => {
  return {
    type: types.SET_PRIZES,
    payload: prizes
  }
};

export const createPrize = prize => {
  return {
    type: types.CREATE_PRIZE,
    payload: prize
  }
};

export const updatePrize = ( id, prize ) => {
  return {
    type: types.UPDATE_PRIZE,
    payload: { id, prize }
  }
};


export const setTemplates = templates => {
  return {
    type: types.SET_TEMPLATES,
    payload: templates
  }
};

export const createTemplate = template => {
  return {
    type: types.CREATE_TEMPLATE,
    payload: template
  }
};

export const updateTemplate = ( id, template ) => {
  return {
    type: types.UPDATE_TEMPLATE,
    payload: { id, template }
  }
};
