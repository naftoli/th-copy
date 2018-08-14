import API from 'api/api';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
import * as actions from './actions';

// get all soldiers
export const getSoldiers = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/core/users' )
    .then( response => {
      dispatch( actions.setLoading( false ) );
      dispatch( actions.setSoldiers( response.data ) );
      return response.data;
    }).catch( () => {
      dispatch( actions.setLoading( false ) );
    });
}
// create new soldier
export const createSoldier = data => dispatch => {
  return API.post( '/core/users', data );
}
// update a single soldier
export const updateSoldier = ( id, data ) => dispatch => {
  const toast_id = createNotifcation('Updating Soldier');
  return API.post( `/core/users?id=${id}`, data )
    .then( response => {
      updateNotifcation( toast_id, 'Soldier Updated!', response.message, response.success );
      if ( response.success ) { 
        dispatch( actions.updateSoldier( id, response.data ) ); 
      } 
      return response;
    }).catch( error => {
      updateNotifcation( toast_id, '', error.message, false );
      return Promise.reject( error );
    });
}

export const deleteSoldier = ( id ) => dispatch => {
  const toast_id = createNotifcation('Deleting Soldier');
  return API.delete( `/core/users?id=${id}`)
    .then( response => {
      updateNotifcation( toast_id, response.data, response.message, response.success );
    });
}

/********************** NON STORE API OPERATIONS **********************/
// load a single soldier - not added to state
export const getSoldier = ( id ) => dispatch => {
  return API.get( `/core/users?id=${id}` )
    .then( response => {
      return response.data;
    })
}
// upload a spreadsheet. does not deal with store
export const uploadProfile = ( formData ) => dispatch => {
  const toast_id = createNotifcation('Uploading Profile Picture...');
  return API.post( '/core/users?action=uploadProfile', formData )
    .then( response => {
      updateNotifcation( toast_id, 'Image Uploaded!', response.message, response.success );
      return response;
    }).catch( error => { 
      updateNotifcation( toast_id, '', error.message, false );
      return Promise.reject( error );
    })
}
// upload a users spreadsheet
export const uploadSpreadsheet = ( data ) => dispatch => {
  const toast_id = createNotifcation('Uploading user spreadsheet...');
  return API.post( `/upload/users`, data )
    .then( response => {
      updateNotifcation( toast_id, 'Spreadsheet Uploaded!', response.message, response.success );
      return response;
    }).catch( error => {
      updateNotifcation( toast_id, '', error.message, false );
      console.error( error );
      return Promise.reject( error );
    });
}
