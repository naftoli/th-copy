import * as types from './types';

export const initialState = {
  soldiers: [], loading: false,
  registration_soldiers: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    // set if we are doing any loading at the moment
    case types.SET_LOADING:
      return { ...state, loading: action.payload };
    // set the list of soldiers
    case types.SET_SOLDIERS:
      return { 
        ...state,
        loading: false,
        soldiers: action.payload
      };
    // update the soldiers for registration
    case types.SET_REGISTRATION_SOLDIERS:
      return {
        ...state,
        loading: false,
        registration_soldiers: action.payload
      };
    // add a soldier to the state at the top of the list
    case types.ADD_SOLDIER:
      return { 
        ...state,
        loading: false,
        soldiers: [ action.payload, ...state.soldiers ]
      };
    // update the soldier if he exists
    case types.UPDATE_SOLDIER:
      let updated_soldiers = state.soldiers.map( 
        soldier => ( soldier.user_id === action.payload.id ? 
          { ...soldier, ...action.payload.updates } : soldier
        )
      );
      // and update the state
      return { ...state, soldiers: updated_soldiers };
    // remove a soldier
    case types.DELETE_SOLDIER:
      return {
        ...state, soldiers: state.soldiers.filter( 
          soldier => soldier.user_id !== action.payload 
        )
      };
    // return the state for other actions
    default:
      return state; 
  }
}
