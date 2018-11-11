import { types } from './actions';

export const initialState = {
  loading: false,
  parshos: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_PARSHOS:
      return { 
        ...state, 
        parshos: action.payload, 
        loading: false
      };

    default:
      return state; 
  }
}
