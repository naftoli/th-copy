import * as types from './types';

export const initialState = { 
  parents: [], 
  children: [],
  loading: false 
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {

    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    
    case types.SET_PARENTS:
      return Object.assign({}, state, {
        parents: action.payload,
        loading: false,
      });

    case types.SET_CHILDREN:
      return Object.assign({}, state, {
        children: action.payload,
        loading: false,
      });
    
    default:
      return state;
  }
}