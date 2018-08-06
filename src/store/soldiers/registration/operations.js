import API from 'api/api';
import { setRegistrationSoldiers, setLoading } from '../actions';
import { createNotifcation, updateNotifcation } from 'functions/notifications';

/**
 * This file updates the soldiers store sections related to soldier registration
 */

// get all soldiers
export const getSoldiers = () => dispatch => {
  dispatch( setLoading( true ) );
  return API.get( '/registration/users' )
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

export const registerSoldiers = ( user_ids, payment, total ) => dispatch => {
  const toast_id = createNotifcation(`Registering ${user_ids.length} Soldiers..`);
  const postData = { user_ids, payment, total };
  return API.post( '/registration/users', postData )
  .then( response => {
    updateNotifcation(
      toast_id, `${user_ids.length} Soldiers Registered for $${total}!`, 
      response.message, response.success 
    );
    debugger;
  }).catch( error => {
    updateNotifcation( toast_id, '', error.message, false );
  })
}
