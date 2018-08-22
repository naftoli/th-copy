/**
 * This file updates store.payments
 */
import API from 'api/api';
import { setLoading, setPayments } from './actions';

// get all of the current logins payments
export const getPaymentProfiles = () => dispatch => {
  dispatch( setLoading( true ) );
  return API.get( '/payments/profiles.php' )
  .then( response => {
    dispatch( setLoading( false ) );
    // go to catch if request has an issue
    if ( !response.success ) 
      return Promise.reject( response );
    dispatch( setPayments( response.data ) );
    // pass data to the next .then();
    return response.data;
  }).catch( error => {
    dispatch( setLoading( false ) );
    return Promise.reject( error );
  });
}
