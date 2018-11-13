import API from 'api/api';
import * as actions from './actions';
import Cookies from 'universal-cookie';
import store from 'store/index';

const cookies = new Cookies();

export const login = opts => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.post( '/auth/login.php', opts )
    .then( ({ legacy, mobile, id }) => {
        dispatch( actions.setTokens( legacy, mobile, id ) );
        getCurrentUser()( dispatch ); // get the user
    })
    .catch( error => {
      dispatch( actions.setLoading( false ) );
      dispatch( actions.setErrors( 
        error.message || 'Could not log in. Please try again.' )
      );
    });
};

export const getCurrentUser = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/auth/current_user.php' )
    .then( user => {
      dispatch( actions.setUser( user ) );
      return setLogin( dispatch );
    }).catch( error => {
      dispatch( actions.logout() );
      dispatch( actions.setErrors( error.message || 
        'Could not get account info. Please clear your cookies and try again.'
      ));
      return Promise.reject( error );
    });
}

export const updateCurrentUser = ( updates ) => dispatch => {

  return API.post( '/auth/current_user.php', updates )
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

const setLogin = dispatch => {
  if ( cookies.get( 'login' ) ) {
    const [ type, id ] = cookies.get( 'login' ).split('-');
    return dispatch( actions.changeLogin( type, parseInt(id, 10) ) );
  } else { 
    return dispatch( actions.changeLogin() );
  }
}

/**
 * validateLogin
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
