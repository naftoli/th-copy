import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';
import Cookies from 'universal-cookie';

const cookies = new Cookies();

export const login = ( username, password ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.post('/auth/login.php', { username, password })
    .then( response => {
      if ( response.success ) {
        const { legacy, mobile, id } = response.data;
        dispatch( actions.setTokens( legacy, mobile, id ) );
        getCurrentUser()( dispatch ); // get the user
      } else {
        dispatch( actions.setLoading( false ) );
        dispatch( actions.setErrors( response.error ) );
      }
    })
    .catch( error => {
      dispatch( actions.setLoading( false ) );
      dispatch( actions.setErrors( 'Could not log in. Please try again.' ));
    });
};

export const getCurrentUser = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/auth/current_user.php' )
    .then( response => {
      dispatch( actions.setLoading( false ) );
      if ( !response.success ) 
        return dispatch( actions.logout() )
      dispatch( actions.setUser( response.data ) )
      if ( cookies.get( 'login' ) ) {
        const [ type, id ] = cookies.get( 'login' ).split('-');
        return dispatch( actions.changeLogin( type, parseInt(id, 10) ) );
      } else { 
        return dispatch( actions.changeLogin() );
      }
    }).catch( error => {
      dispatch( actions.logout() );
      dispatch( actions.setErrors( 'Could not get account info. Please clear your cookies and try again.' ));
    });
}

export const deleteAuth = ( auth ) => dispatch => {
  return API.delete( '/core/admin_auths', auth )
  .then( handleAPIResponse )
  .then( data => dispatch( actions.removeAuth( data.auth, data.id ) ) );
}

export const createAuth = ( auth ) => dispatch => {
  return API.post( '/core/admin_auths', auth )
  .then( handleAPIResponse )
}
