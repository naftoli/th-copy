import API from 'api/api';
import * as actions from './actions';
import Cookies from 'universal-cookie';
import store from 'store/index';

const cookies = new Cookies();

/**
 * try to login with these credentials
 * @param {object} opts params to send to auth/login
 */
export const login = opts => dispatch => {
  dispatch( actions.setLoading( true ) );
  
  return API.post( '/auth/login', opts )
    .then( ({ legacy, mobile, id }) => {
        return dispatch( actions.setTokens( legacy, mobile, id ) );
    })
    .catch( error => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( error );
    });
};

export const createAccount = opts => dispatch => {
  return API.post( '/auth/new_account', opts )
    .then( ({ account, tokens }) => {
      // set the tokens to log the user in
      dispatch( actions.setTokens( tokens.legacy, tokens.mobile, tokens.id ) );
      // and set the users account information at the same time
      dispatch( actions.setUser( account ) );
      return setLogin( dispatch );
    });
}

/**
 * send a reset email to this account.
 * @param {string} email email address
 */
export const sendResetRequest = email => {
  return API.post( '/auth/forgot', { email } )
}

/**
 * load the information for the current user
 */
export const getCurrentUser = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  
  return API.get( '/auth/current_user' )
    .then( user => {
      dispatch( actions.setUser( user ) );
      return setLogin( dispatch );
    }).catch( error => {
      dispatch( actions.logout() );
      return Promise.reject( error );
    });
}

/**
 * update the current user
 * @param {object} updates the parts of the current user we want to update
 */
export const updateCurrentUser = updates => dispatch => {

  return API.post( '/auth/current_user', updates )
    .then( data => {
      dispatch( actions.setUser( data.account ) );

      const { legacy, mobile, id } = data.tokens;
      dispatch( actions.setTokens( legacy, mobile, id ) );

      setLogin( dispatch );
      return data;
    }).catch( error => {
      return Promise.reject( error );
    });
}

/**
 * connect the chabad.org or google key to this account
 * @param {string} type google or chabad
 * @param {string} key chabad.org login key
 */
export const connectExternalAccount = ( type, key ) => dispatch => {
  return API.post( '/auth/current_user?action=connectAccount', { type, key } )
    .then( updates => dispatch( actions.updateUser( updates ) ) );
}

/**
 * disconnect the chabad.org key from this account
 * @param {string} type google or chabad
 */
export const disconnectExternalAccount = type => dispatch => {
  return API.post( '/auth/current_user?action=disconnectAccount', { type } )
    .then( updates => dispatch( actions.updateUser( updates ) ) );
}

/**
 * set the current login, used in functions above.
 * @param {function} dispatch the dispatch callback from the store
 */
const setLogin = dispatch => {
  if ( cookies.get( 'login' ) ) {
    const [ type, id ] = cookies.get( 'login' ).split('-');
    return dispatch( actions.changeLogin( type, parseInt(id, 10) ) );
  } else { 
    return dispatch( actions.changeLogin() );
  }
}

/**
 * validateLogin ( runs every few seconds )
 * 
 * validate that the current login is correct and update it if not.
 */
export const validateLogin = () => {
  const { current_login } = store.getState().login;

  // * conditions under which we do nothing
  if (
    !current_login    ||  !cookies.get( 'login' ) ||
    !current_login.id ||  !current_login.type
  ) return true;

  // * get the current tokens
  const current_tokens = cookies.get( 'login' ).split('-');
  // extract the data
  const type = current_tokens[0];
  const id = parseInt( current_tokens[1], 10 );
  
  // * check for changes
  if ( // if the type or id no longer match
    current_login.id !== id ||
    current_login.type !== type
  ) { // then update this instance to match the correct login type
    getCurrentUser()( store.dispatch );
  }
}
