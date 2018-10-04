import API, { handleAPIResponse } from 'api/api';
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
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}
// create new soldier
export const createSoldier = data => dispatch => {
  return API.post( '/core/users', data );
}
// update a single soldier
export const updateSoldier = ( id, data ) => dispatch => {
  return API.post( `/core/users?id=${id}`, data )
    .then( response => {
      if ( response.success ) { 
        dispatch( actions.updateSoldier( id, response.data ) ); 
      } 
      return response;
    }).catch( error => {
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
// upload a profile picture. does not deal with store
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

/**
 * this function is used in platoonSelect only
 */
export const getSoldierList = ( class_id = false ) => {
  return API.post( `/core/users?action=small`, { class_id } )
  .then( handleAPIResponse );
}
