import * as types from './types';

export const initialState = {
  soldiers: [], loading: false,
  registration_soldiers: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    // set if we are doing any loading at the moment
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    // set the list of soldiers
    case types.SET_SOLDIERS:
      return Object.assign({}, state, {
        soldiers: action.payload
      });
    // update the soldiers for registration
    case types.SET_REGISTRATION_SOLDIERS:
      return Object.assign({}, state, {
        registration_soldiers: action.payload
      });
    // update the soldier if he exists
    case types.UPDATE_SOLDIER:
      let updated_soldiers = state.soldiers.map( 
        soldier => ( soldier.user_id === action.payload.id ? Object.assign( {}, soldier, action.payload.updates ) : soldier )
      );
      // and update the state
      return Object.assign({}, state, { soldiers: updated_soldiers });
    // remove a soldier
    case types.DELETE_SOLDIER:
      return Object.assign({}, state, { 
        soldiers: state.soldiers.filter( soldier => soldier.user_id !== action.payload )
      });
    // return the state for other actions
    default:
      return state; 
  }
}