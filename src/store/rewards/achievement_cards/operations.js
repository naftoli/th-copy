import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

// generate cards
export const generateAchievementCards = ( data ) => dispatch => {
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
