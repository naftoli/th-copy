import API from 'api/api';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
import * as actions from './actions';

// get all soldiers
export const getSoldiers = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/core/users' )
    .then( soldiers => {
      dispatch( actions.setSoldiers( soldiers ) );
      return soldiers;
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}

// create new soldier
export const createSoldier = data => dispatch => {
  return API.post( '/core/users', data )
    .then( soldier => dispatch( actions.addSoldier( soldier ) ) );
}

// update a single soldier
export const updateSoldier = ( id, data ) => dispatch => {
  return API.post( `/core/users?id=${id}`, data )
    .then( soldier => {
      dispatch( actions.updateSoldier( id, soldier ) );  
      return soldier;
    });
}


export const deleteSoldier = ( id ) => dispatch => {
  return API.delete( `/core/users?id=${id}`);
}

/********************** NON STORE API OPERATIONS **********************/

// load a single soldier - not added to state
export const getSoldier = ( id ) => dispatch => {
  return API.get( `/core/users?id=${id}` )
}

// upload a profile picture. does not deal with store
export const uploadProfile = ( formData ) => dispatch => {
  return API.post( '/core/users?action=uploadProfile', formData );
}

export const updateMissions = ( user_id, subjects ) => dispatch => {
  return API.post( '/core/users?action=updateMissions', { user_id, subjects } );
}

// upload a users spreadsheet
export const uploadSpreadsheet = ( data ) => dispatch => {
  const toast_id = createNotifcation('Uploading user spreadsheet...');
  return API.post( `/upload/users`, data )
    .then( response => {
      updateNotifcation( toast_id, 'Spreadsheet Uploaded!', true );
      return response;
    }).catch( error => {
      updateNotifcation( toast_id, '', error.message, false );
      return Promise.reject( error );
    });
}
