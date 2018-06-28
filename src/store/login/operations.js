import API from 'api/api';
import * as actions from './actions';

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
      dispatch( actions.setErrors( "Network Error" ));
    });
};

export const getCurrentUser = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/auth/current_user.php' )
    .then( response => {
      dispatch( actions.setLoading( false ) );
      if ( !response.success ) 
        return dispatch( actions.logout() )
      return dispatch( actions.setUser( response.data ) );
    });
}
