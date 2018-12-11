import * as types from './types';

export const initialState = { 
  staff: [],
  loading: false 
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {

    case types.SET_LOADING:
      return {
        ...state,
        loading: action.payload
      };
    
    case types.SET_STAFF:
      return { 
        ...state,
        staff: action.payload,
        loading: false,
      };

    case types.UPDATE_STAFF:
      return {
        ...state,
        staff: state.staff.map( staff => {
          if ( staff.admin_id === action.payload.admin_id )
            return { ...staff, ...action.payload.updates };
          return staff;
        })
      };

    case types.REMOVE_AUTH:
      return {
        ...state,
        staff: state.staff.map( staff => {
          if ( staff.admin_id === action.payload.admin_id ) {
            return { ...staff, // filter the positions to remove the payload
              positions: staff.positions.filter(
                ({ id, auth }) => !( id === action.payload.id && auth === action.payload.auth ) 
              )
            };
          }
          return staff;
        })
      };

    case types.CREATE_AUTH:
      return {
        ...state,
        staff: state.staff.map( staff => {
          if ( staff.admin_id === action.payload.admin_id )
            return { // if we are on the right admin add the payload
              ...staff, 
              positions: staff.positions.concat( action.payload )
            };
          return staff;
        })
      };
    
    default:
      return state;
  }
}
