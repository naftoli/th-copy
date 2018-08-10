import * as types from './types';

export const initialState = {
  platoons: [],
  loading: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    // set the loading screen
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    // update the platoons
    case types.SET_PLATOONS:
      return Object.assign({}, state, {
        platoons: action.payload
      });
    // update a single platoon
    case types.UPDATE_PLATOON:
      let updated_platoons = state.platoons.map( platoon => 
        ( platoon.class_id === action.payload.id ? 
          Object.assign( {}, platoon, action.payload.updates ) : platoon
        )
      );
      return Object.assign({}, state, { platoons: updated_platoons });
    // no change
    default:
      return state; 
  }
}
