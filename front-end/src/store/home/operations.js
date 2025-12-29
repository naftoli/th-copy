import API from 'api/api';
import * as actions from './actions';

export const getRegistration = () => dispatch => {
  return API.get( `/core/homepage/registration` )
    .then( data => dispatch( actions.setRegistration( data ) ) );
}

export const getBirthdays = (fromDate = null, toDate = null) => dispatch => {
  let url = `/core/homepage/birthdays`;
  
  if (fromDate && toDate) {
    url += `?start=${fromDate}&end=${toDate}`;
  }
  
  return API.get( url )
    .then( birthdays => dispatch( actions.setBirthdays( birthdays ) ) );
}

export const getPromotions = (fromDate = null, toDate = null) => dispatch => {
  let url = `/core/homepage/promotions`;
  
  if (fromDate && toDate) {
    url += `?start=${fromDate}&end=${toDate}`;
  }
  
  return API.get( url )
    .then( promotions => dispatch( actions.setPromotions( promotions ) ) );
}
