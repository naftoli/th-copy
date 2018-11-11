import API from 'api/api';

/**
 * THIS FILE DOES NOT UPDATE THE STATE.
 * 
 * ALL FUNCTIONS CONTAINED WITHIN DO NOT CURRENTLY SUPPORT REDUX
 */

// get all soldiers
export const getRankCards = ( options ) => {
  return API.post( '/core/id_cards', options );
}

export const markPrinted = updates => {
  return API.post( '/core/id_cards?action=markPrinted', { updates } )
}
