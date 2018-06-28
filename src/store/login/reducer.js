import * as types from './types';
import Cookies from 'universal-cookie';
const cookies = new Cookies();

export const initialState = {
  current_user: false,
  current_login: {},
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
      if ( action.payload.legacy ) {
        cookies.set( 'admin_auth', action.payload.legacy, { path: '/' } );
        cookies.set( 'admin_id', action.payload.id, { path: '/' } );
        cookies.set( 'admin', action.payload.mobile, { path: '/' } );
      }

      return Object.assign({}, state, { tokens: action.payload });

    case types.SET_USER:
      return Object.assign({}, state, {
        current_user: action.payload,
        current_login: action.payload.logins[0]
      });
    
    case types.LOGOUT:
      cookies.remove( 'admin_auth', { path: '/' } );
      cookies.remove( 'admin_id', { path: '/' } );
      cookies.remove( 'admin', { path: '/' } );
      return Object.assign( {}, initialState );

    case types.CHANGE_LOGIN:
      const { type, id } = action.payload;
      const login = state.current_user.logins.find( login => login.type === type && login.id === id )
      return Object.assign( {}, state, { current_login: login });

    default:
      return state; 
  }
}
