import * as types from './types';

export const initialState = { 
  staff: [],
  loading: false 
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {

    case types.SET_LOADING:
      return {
        ...state,
        loading: action.payload
      };
    
    case types.SET_STAFF:
      return { 
        ...state,
        staff: action.payload,
        loading: false,
      };
    
    default:
      return state;
  }
}
