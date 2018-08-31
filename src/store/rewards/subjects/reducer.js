import * as types from './types';

export const initialState = {
  loading: false,
  subjects: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { ...state, loading: action.payload };

    case types.SET_SUBJECTS:
      return { ...state, subjects: action.payload, loading: false };

    default:
      return state; 
  }
}
