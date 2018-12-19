import API from 'api/api';
import * as actions from './actions';

/**
 * getMiles
 * 
 * get the total amount of miles that can be used
 */
export const getMiles = () => dispatch => {
  return API.get( `/rewards/miles` )
  .then( miles => dispatch( actions.setMiles( miles ) ) );
}

/**
 * deleteUnused
 * 
 * delete cards that where not reddemed before a given date
 * 
 * @param {date} delete_to date to delete untill
 */
export const deleteUnusedCards = delete_to => dispatch => {
  return API.delete( `/rewards/achievement_cards`, { delete_to } )
  .then( ({ miles, cards_deleted }) => {
    dispatch( actions.setMiles( miles ) );
    return cards_deleted;
  });
}
