import API from 'api/api';
import * as actions from './actions';
import Cookies from 'universal-cookie';
import { createNotifcation, updateNotifcation } from 'functions/notifications';

const cookies = new Cookies();

export const login = ( username, password ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.post( '/auth/login.php', { username, password } )
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
      dispatch( actions.setLoading( false ) );
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

  const toast_id = createNotifcation('Updating Account');

  return API.post( '/auth/current_user.php', updates )
    .then( data => {
      updateNotifcation( toast_id, 'Account Updated', '', true );
      dispatch( actions.setUser( data.account ) );

      const { legacy, mobile, id } = data.tokens;
      dispatch( actions.setTokens( legacy, mobile, id ) );

      setLogin( dispatch );
      return data;
    }).catch( error => {
      updateNotifcation( toast_id, '', error.message, false );
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
