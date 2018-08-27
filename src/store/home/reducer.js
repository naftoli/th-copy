import * as types from './types';

export const initialState = {
  registration: {}
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_REGISTRATION:
      return { ...state, registration: action.payload };

    default:
      return state; 
  }
}
