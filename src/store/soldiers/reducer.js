import * as types from './types';

export const initialState = {
  soldiers: [],
  loading: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    case types.SET_SOLDIERS:
      return Object.assign({}, state, {
        soldiers: action.payload
      });
    default:
      return state; 
  }
}