import * as types from './types';

export const initialState = {
  loading: false,
  orders: [],
  store: false
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

    case types.SET_STORE:
      return { 
        ...state, 
        store: action.payload, 
      };

    case types.ADD_ORDER:
      return {
        ...state,
        orders: [ action.payload, ...state.orders ] 
      };

    default:
      return state; 
  }
}
