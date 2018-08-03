/**
 * This file updates the soldiers store sections related to soldier registration
 */
import API from 'api/api';
import { setRegistrationSoldiers, setLoading } from '../actions';

// get all soldiers
export const getSoldiers = () => dispatch => {
  dispatch( setLoading( true ) );
  return API.get( '/registration/users.php' )
  .then( response => {
    dispatch( setLoading( false ) );
    // go to catch if request has an issue
    if ( !response.success ) 
      return Promise.reject( response );
    dispatch( setRegistrationSoldiers( response.data ) );
    // pass data to the next .then();
    return response.data;
  }).catch( error => {
    dispatch( setLoading( false ) );
    return Promise.reject( error );
  });
}