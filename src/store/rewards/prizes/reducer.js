import * as types from './types';

export const initialState = {
  loading: false,
  prizes: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_PRIZES:
      return { 
        ...state, 
        prizes: action.payload, 
        loading: false
      };

    // add prize to top of list becuase it is new ;-)
    case types.CREATE_PRIZE:
      return { 
        ...state, 
        prizes: [ action.payload, ...state.prizes ], 
      };

    case types.UPDATE_PRIZE:
      return { 
        ...state, 
        prizes: state.prizes.map( prize => {
          if ( prize.prize_id === action.payload.id )
            return action.payload.prize;
          return prize;
        }),
      };

    default:
      return state; 
  }
}
