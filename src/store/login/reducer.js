import { types } from './actions';

export const initialState = {
  current_user: {},
  loading: false,
  errors: [],
  tokens: {}
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    case types.SET_ERRORS:
      return Object.assign({}, state, {
        errors: action.payload
      });
    case types.SET_LOADING:
      return Object.assign({}, state, {
        loading: action.payload
      });
    case types.SET_TOKENS:
      return Object.assign({}, state, {
        tokens: action.payload
      });
    case types.SET_USER:
      return Object.assign({}, state, {
        current_user: action.payload
      });
    default:
      return state; 
  }
}