import * as types from './types';

export const initialState = {
  loading: false,
  prizes: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_PRIZES:
      return { 
        ...state, 
        prizes: action.payload, 
        loading: false
      };

    default:
      return state; 
  }
}
