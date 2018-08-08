import API from 'api/api';

/**
 * THIS FILE DOES NOT UPDATE THE STATE.
 * 
 * ALL FUNCTIONS CONTAINED WITHIN DO NOT CURRENTLY SUPPORT REDUX
 */

// get all soldiers
export const getRankCards = ( options ) => {
  return API.post( '/core/id_cards', options )
  .then( response => {
    if ( !response.success ) 
      return Promise.reject( response );
    return response.data;
  });
}

export const markPrinted = ( data ) => {
  debugger;
  return API.post( '/core/id_cards?action=markPrinted', data )
  .then( response => {
    debugger;
  });
}
