import * as types from './types';
// import _ from 'lodash';

export const initialState = {};

export default ( state = initialState, action ) => {
  const { type, payload } = action
  switch ( type ) {
    // set if we are doing any loading at the moment
    case types.SET_MODULE_LOADING:
      return { ...state, [payload.module]: {...(state[payload.module] || {}), loading: payload.loading} };
    // set the module data
    case types.SET_MODULE:
      return {  ...state, [payload.module]: { loading: false, ...payload.data} };
    default:
      return state; 
  }
}
