import { types } from './actions';

export const initialState = {
  institutions: [],
  loading: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return {
        ...state,
        loading: action.payload
      };

    case types.SET_INSTS:
      return {
        ...state,
        institutions: action.payload
      };

    default:
      return state; 
  }
}
