import * as types from './types';

export const initialState = {
  loading: {
    prizes: false,
    templates: false
  },
  prizes: [],
  templates: [],
  school_store: true
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: { ...state.loading, ...action.payload } 
      };

    case types.SET_STORE_OPEN:
      return { 
        ...state, 
        school_store: action.payload
      };

    case types.SET_PRIZES:
      return { 
        ...state, 
        prizes: action.payload, 
        loading: { ...state.loading, prizes: false } 
      };

    case types.SET_TEMPLATES:
      return { 
        ...state, 
        templates: action.payload, 
        loading: { ...state.loading, templates: false } 
      };

    // add prize to top of list becuase it is new ;-)
    case types.CREATE_PRIZE:
      return { 
        ...state, 
        prizes: [ action.payload, ...state.prizes ], 
      };

    case types.CREATE_TEMPLATE:
      return { 
        ...state, 
        templates: [ action.payload, ...state.templates ], 
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

    case types.DELETE_PRIZE:
      console.log('Reducer: Deleting prize with ID:', action.payload);
      console.log('Current prizes count:', state.prizes.length);
      const filteredPrizes = state.prizes.filter( prize => prize.prize_id !== action.payload );
      console.log('Filtered prizes count:', filteredPrizes.length);
      return { 
        ...state, 
        prizes: filteredPrizes
      };

    case types.UPDATE_TEMPLATE:
      return { 
        ...state, 
        templates: state.templates.map( template => {
          if ( template.prize_id === action.payload.id )
            return action.payload.template;
          return template;
        }),
      };

    case types.UPDATE_PRIZE_LOCALLY:
      return {
        ...state,
        prizes: state.prizes.map(prize => {
          if (prize.prize_id === action.payload.id)
            return { ...prize, ...action.payload.updates };
          return prize;
        })
      };

    default:
      return state; 
  }
}
