/**
 * This file updates store.payments
 */
import API from 'api/api';
import { setLoading, setPayments } from './actions';

// get all of the current logins payments
export const getPaymentProfiles = () => dispatch => {
  dispatch( setLoading( true ) );
  return API.get( '/payments/profiles.php' )
  .then( payments => {
    dispatch( setPayments( payments ) );
    // pass data to the next .then();
    return payments;
  }).catch( error => {
    dispatch( setLoading( false ) );
    return Promise.reject( error );
  });
}
