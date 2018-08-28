import * as types from './types';

export const initialState = {
  registration: {},
  birthdays: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_REGISTRATION:
      return { ...state, registration: action.payload };

    case types.SET_BIRTHDAYS:
      return { ...state, birthdays: action.payload };

    default:
      return state; 
  }
}
