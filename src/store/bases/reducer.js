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
      let updated_bases = state.bases.concat( action.payload.updates );
      action.payload.id = parseInt( action.payload.id, 10 );
      // if we have the base in our bases array, update that base.
      if ( state.bases.find( base => base.class_id === action.payload.id ) ) {
        updated_bases = state.bases.map( 
          base => ( base.class_id === action.payload.id ?
            Object.assign( {}, base, action.payload.updates ) :
            base
          )
        );
      }
      // and update the state
      return Object.assign({}, state, {
        bases: updated_bases
      });
    default:
      return state; 
  }
}
