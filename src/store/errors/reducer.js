import * as types from './types';

export const initialState = [];

export const reducer = ( state = initialState, action ) => {
  switch ( action.type ) {
    case types.ADD_ERROR:
      return state.concat( action.payload );
    case types.CLEAR_ERROR:
      return state.filter( error => error.id !== action.payload );
    default:
      return state;
  }
}

export default reducer;