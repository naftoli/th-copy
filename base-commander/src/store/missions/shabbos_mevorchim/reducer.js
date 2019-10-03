import { types } from './actions';

export const initialState = {
  loading: false,
  months: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_MONTHS:
      return { 
        ...state, 
        months: action.payload, 
        loading: false
      };

    default:
      return state; 
  }
}
