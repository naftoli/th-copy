import { types } from './actions';

export const initialState = {
  miles: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
  
    case types.SET_MILES:
      return { 
        ...state, 
        miles: action.payload
      };

    default:
      return state; 
  }
}
