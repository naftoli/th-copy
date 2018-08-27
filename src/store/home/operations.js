import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';
// functions
// import { createNotifcation, updateNotifcation } from 'functions/notifications';

// get all bases
export const getRegistration = () => dispatch => {
  return API.get( `/core/homepage/registration` )
    .then( handleAPIResponse )
    .then( data => dispatch( actions.setRegistration( data ) ) );
}
