import * as types from './types';

export const initialState = {
  platoons: [],
  loading: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    // set the loading screen
    case types.SET_LOADING:
      return {
        ...state,
        loading: action.payload
      };
    // update the platoons
    case types.SET_PLATOONS:
      return {
        ...state,
        platoons: action.payload
      };
    // update a single platoon
    case types.UPDATE_PLATOON:
      return {
        ...state, 
        platoons: state.platoons.map( 
          platoon => ( platoon.class_id === action.payload.id ? {...platoon, ...action.payload.updates } : platoon )
        )
      };
    // // add a platoon when it is created
    // case types.CREATE_PLATOON:
    //   return {
    //     ...state, 
    //     platoons: [ action.payload, ...state.platoons ] 
    //   };
    // no change
    default:
      return state; 
  }
}
