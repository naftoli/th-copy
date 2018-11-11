import API from 'api/api';
import * as actions from './actions';

export const getRegistration = () => dispatch => {
  return API.get( `/core/homepage/registration` )
    .then( data => dispatch( actions.setRegistration( data ) ) );
}

export const getBirthdays = () => dispatch => {
  return API.get( `/core/homepage/birthdays` )
    .then( birthdays => dispatch( actions.setBirthdays( birthdays ) ) );
}

export const getPromotions = () => dispatch => {
  return API.get( `/core/homepage/promotions` )
    .then( promotions => dispatch( actions.setPromotions( promotions ) ) );
}
