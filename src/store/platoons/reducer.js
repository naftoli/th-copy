import * as types from './types';

export const initialState = {
  platoons: [],
  loading: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    case types.SET_PLATOONS:
      return Object.assign({}, state, {
        platoons: action.payload
      });
    case types.UPDATE_PLATOON:
      let updated_platoons = state.platoons.concat( action.payload.updates );
      action.payload.id = parseInt( action.payload.id, 10 );
      // if we have the platoon in our platoons array, update that platoon.
      if ( state.platoons.find( platoon => platoon.class_id === action.payload.id ) ) {
        updated_platoons = state.platoons.map( 
          platoon => ( platoon.class_id === action.payload.id ?
            Object.assign( {}, platoon, action.payload.updates ) :
            platoon
          )
        );
      }
      // and update the state
      return Object.assign({}, state, {
        platoons: updated_platoons
      });
    default:
      return state; 
  }
}
