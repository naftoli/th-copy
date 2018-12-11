import * as types from './types';

export const initialState = {
  birthdays: false,
  registration: {},
  promotions: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_REGISTRATION:
      return { ...state, registration: action.payload };

    case types.SET_BIRTHDAYS:
      return { ...state, birthdays: action.payload };
    
    case types.SET_PROMOTIONS:
      return { ...state, promotions: action.payload };

    default:
      return state; 
  }
}
