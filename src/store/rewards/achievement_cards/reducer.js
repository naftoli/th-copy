import * as types from './types';

export const initialState = {
  loading: false,
  cards: [],
  miles: false
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_CARDS:
      return { 
        ...state, 
        cards: action.cards, 
        loading: false
      };
    
    case types.SET_MILES:
      return { 
        ...state, 
        miles: action.payload
      };

    default:
      return state; 
  }
}
