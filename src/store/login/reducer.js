import { types } from './actions';
import Cookies from 'universal-cookie';
const cookies = new Cookies();

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
      cookies.set( 'admin_auth', action.payload.legacy, { path: '/' } );
      return Object.assign({}, state, {
        tokens: action.payload
      });
    case types.SET_USER:
      cookies.set( 'admin_id', action.payload.admin_id, { path: '/' } );
      return Object.assign({}, state, {
        current_user: action.payload
      });
    case types.LOGOUT:
      cookies.remove( 'admin_auth' );
      cookies.remove( 'admin_id' );
      return Object.assign( {}, initialState );
    default:
      return state; 
  }
}