import * as types from './types';

export const initialState = {
  bases: [],
  loading: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });

    case types.SET_BASES:
      return Object.assign({}, state, {
        bases: action.payload
      });

    case types.UPDATE_BASE:
      return {
        bases: state.bases.map( 
          base => ( base.school_id === action.payload.id ? { ...base, ...action.payload.updates } : base )
        )
      };

    default:
      return state; 
  }
}
