import API from 'api/api';
import { createNotifcation, updateNotifcation } from 'functions/notifications';

/**
 * THIS FILE DOES NOT UPDATE THE STATE.
 * 
 * ALL FUNCTIONS CONTAINED WITHIN DO NOT CURRENTLY SUPPORT REDUX
 */

const handleResponse = response => {
  if ( !response.success ) 
    return Promise.reject( response );
  return response.data;
}

// get all soldiers
export const getRankCards = ( options ) => {
  return API.post( '/core/id_cards', options )
  .then( handleResponse );
}

export const markPrinted = updates => {
  const toast_id = createNotifcation(`Marking ${updates.length} cards printed status...`);
  return API.post( '/core/id_cards?action=markPrinted', { updates } )
  .then( response => { 
    updateNotifcation(toast_id, `Marked ${updates.length} cards printing status`, response.error, response.success );
    return response;
  }).then( handleResponse )
  .catch( error => updateNotifcation(toast_id, '', error.error, false ) );
}
