import * as types from './types';
// TODO handle when positions change in the UI
// import { REMOVE_AUTH } from '../staff/types';
import Cookies from 'universal-cookie';
const cookies = new Cookies();

export const initialState = {
  current_user: false,
  current_login: {},
  loading: false,
  errors: [],
  tokens: {},
  title: false
};

export default ( state = initialState, action ) => {
  const expires = new Date();
  expires.setFullYear(new Date().getFullYear() + 10);

  switch ( action.type ) {

    // set any login errors
    case types.SET_ERRORS:
      return { ...state, errors: action.payload };

    // set the loading staus
    case types.SET_LOADING:
      return { ...state, loading: action.payload };

    // set the tokens
    case types.SET_TOKENS:
      if ( action.payload.legacy ) {
        cookies.set( 'admin_auth', action.payload.legacy, { path: '/', expires } );
        cookies.set( 'admin_id', action.payload.id, { path: '/', expires } );
      }
      return { ...state, tokens: action.payload };

    // set the current user
    case types.SET_USER:
      // user is a different portal, do not update the cookies.
      if ( action.payload.logins[0].type !== 'PARENT' && !cookies.get('login') ) {
        const login = action.payload.logins[0];
        cookies.set( 'login', `${login.type}-${login.id}`, { path: '/' } );
      }
      return Object.assign({}, state, {
        current_user: action.payload
      });

    // set the page title
    case types.SET_TITLE:
      return { ...state, title: action.payload }
    
    // logout the user
    case types.LOGOUT:
      cookies.remove( 'admin_auth', { path: '/' } );
      cookies.remove( 'admin_id', { path: '/' } );
      cookies.remove( 'admin', { path: '/' } );
      cookies.remove( 'login', { path: '/' } );
      return { ...initialState };

    // change the login
    case types.CHANGE_LOGIN:
      const { type, id } = action.payload;
      const current_login = state.current_user.logins.find( 
        login => login.type === type && login.id === id // find a login with the same type and id
      ) || state.current_user.logins[0]; // or just get the first one
      
      // user is a different portal, do not update the cookies.
      if ( current_login.type !== 'PARENT' ) {
        cookies.set( 'login', `${current_login.type}-${current_login.id}`, { path: '/', expires } );
      } else {
        cookies.set( 'admin', current_login.key, { path: '/', expires } );
      }
      // non-destructivly return the state
      return { ...state, current_login };

    default:
      return state; 
  }
}
