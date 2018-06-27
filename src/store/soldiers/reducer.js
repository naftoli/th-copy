import * as types from './types';

export const initialState = {
  soldiers: [],
  loading: false
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
    case types.UPDATE_SOLDIER:
      // update the details on the edited user in the list of users
      const updated_soldiers = state.soldiers.map( 
        soldier => ( soldier.user_id === action.payload.id ? Object.assign( {}, soldier, action.payload.updates ) : soldier )
      );
      // and update the state
      return Object.assign({}, state, {
        soldiers: updated_soldiers
      });
    default:
      return state; 
  }
}