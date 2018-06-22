import API from 'api/api';
import * as actions from './actions';

export const login = ( username, password ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.post('/auth/login.php', { username, password })
    .then( response => {
      dispatch( actions.setLoading( false ) );
      if ( response.success ) {
        dispatch( actions.setTokens( response.data.legacy, response.data.mobile ));
        dispatch( actions.setUser( response.data.user ));
      } else {
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
        return dispatch( actions.setTokens( false, false ) )
      return dispatch( actions.setUser( response.data ) );
    });
}
