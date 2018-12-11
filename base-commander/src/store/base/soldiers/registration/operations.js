import API from 'api/api';
import { setRegistrationSoldiers, setLoading } from '../actions';

/**
 * This file updates the soldiers store sections related to soldier registration
 */

// get all soldiers
export const getSoldiers = () => dispatch => {
  dispatch( setLoading( true ) );

  return API.get( '/registration/users' )
    .then( soldiers => {
      dispatch( setRegistrationSoldiers( soldiers ) );
      // pass data to the next .then();
      return soldiers;
    }).catch( error => {
      dispatch( setLoading( false ) );
      return Promise.reject( error );
    });
}

export const registerSoldiers = ( user_ids, payment, total ) => dispatch => {
  const postData = { user_ids, payment, total };
  return API.post( '/registration/users', postData )
}
