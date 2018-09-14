import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

// generate cards
export const generateAchievementCards = ( data ) => dispatch => {
  dispatch( actions.setCards([]) );
  dispatch( actions.setLoading( true ) );
  return API.post( `/rewards/achievement_cards`, data )
    .then( handleAPIResponse )
    .then( ({ cards, miles }) => {
      dispatch( actions.setCards( cards ) );
      dispatch( actions.setMiles( miles ) );
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}

export const getMiles = () => dispatch => {
  return API.get( `/rewards/achievement_cards` )
  .then( ({ data }) => { 
    dispatch( actions.setMiles( data.miles ) ); 
    return data;
  });
}

export const deleteUnused = delete_to => dispatch => {
  return API.delete( `/rewards/achievement_cards`, { delete_to } )
  .then( handleAPIResponse )
  .then( ({ miles, cards_deleted }) => {
    dispatch( actions.setMiles( miles ) );
    dispatch( actions.setCards( [] ) );
    return cards_deleted;
  });
}
