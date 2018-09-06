import * as types from './types';

export const initialState = {
  loading: {
    prizes: false,
    templates: false
  },
  prizes: [],
  templates: [],
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: { ...state.loading, ...action.payload } 
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

    case types.UPDATE_TEMPLATE:
      return { 
        ...state, 
        templates: state.templates.map( template => {
          if ( template.prize_id === action.payload.id )
            return action.payload.template;
          return template;
        }),
      };

    default:
      return state; 
  }
}
