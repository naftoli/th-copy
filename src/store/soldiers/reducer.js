import * as types from './types';

export const initialState = {
  soldiers: [], loading: false,
  registration_soldiers: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    case types.SET_SOLDIERS:
      return Object.assign({}, state, {
        soldiers: action.payload
      });
    case types.SET_REGISTRATION_SOLDIERS:
      return Object.assign({}, state, {
        registration_soldiers: action.payload
      });
    case types.UPDATE_SOLDIER:
      let updated_soldiers = state.soldiers.concat( action.payload.updates );
      action.payload.id = parseInt( action.payload.id, 10 );
      if ( state.soldiers.find( soldier => soldier.user_id === action.payload.id ) ) {
        // update the details on the edited user in the list of users
        updated_soldiers = state.soldiers.map( 
          soldier => ( soldier.user_id === action.payload.id ? Object.assign( {}, soldier, action.payload.updates ) : soldier )
        );
      }
      // and update the state
      return Object.assign({}, state, {
        soldiers: updated_soldiers
      });
    default:
      return state; 
  }
}