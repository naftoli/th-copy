import * as types from './types';

export const initialState = {
  loading: false,
  orders: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_ORDERS:
      return { 
        ...state, 
        orders: action.payload, 
        loading: false
      };

    default:
      return state; 
  }
}
