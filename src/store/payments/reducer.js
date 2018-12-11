import * as types from './types';

export const initialState = { 
  payments: [], 
  loading: false 
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {

    case types.SET_LOADING:
      return { 
        ...state,
        loading: action.payload
      };
    
    case types.SET_PAYMENTS:
      return { 
        ...state,
        loading: false,
        payments: action.payload
      };
    
    default:
      return state; 
  }
}
