import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getRegistration = () => dispatch => {
  return API.get( `/core/homepage/registration` )
    .then( handleAPIResponse )
    .then( data => dispatch( actions.setRegistration( data ) ) );
}

export const getBirthdays = () => dispatch => {
  return API.get( `/core/homepage/birthdays` )
    .then( handleAPIResponse )
    .then( birthdays => dispatch( actions.setBirthdays( birthdays ) ) );
}
