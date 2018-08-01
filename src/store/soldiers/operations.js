import API from 'api/api';
import { toast } from 'react-toastify';
import moment from 'moment';
import * as actions from './actions';

const createNotifcation = ( message ) => {
  return toast.info( 'Updating Soldier...', { autoClose: false } );
}

const updateNotifcation = ( toast_id, message, error = '', success = true ) => {
  const { SUCCESS, ERROR } = toast.TYPE;
  toast.update( toast_id, { 
    type: success ? SUCCESS : ERROR, 
    render: success ? message : error,
    autoClose: success ? null : false
  }); 
  return success;
}

// get all soldiers
export const getSoldiers = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/core/users.php' )
    .then( response => {
      var t0 = performance.now();
      // format data
      const soldiers = response.data.map( soldier => Object.assign({}, soldier, { 
        dob: soldier.dob ? moment( soldier.dob ).format('l') : soldier.dob,
        user_registered: soldier.user_registered ? 
          moment( soldier.user_registered ).format('l LT') : 
          soldier.user_registered
      }));
      console.log("Formatting users response took " + (performance.now() - t0) + " milliseconds. TODO, speed up.");
      dispatch( actions.setLoading( false ) );
      return dispatch( actions.setSoldiers( soldiers ) );
    }).catch( () => {
      dispatch( actions.setLoading( false ) );
    });
}
// create new soldier
export const createSoldier = data => dispatch => {
  return API.post( '/core/users.php', data );
}
// update a single soldier
export const updateSoldier = ( id, data ) => dispatch => {
  const toast_id = createNotifcation('Updating Soldier');
  return API.post( `/core/users.php?id=${id}`, data )
    .then( response => {
      updateNotifcation( toast_id, 'Soldier Updated!', response.error, response.success)
      if ( response.success ) { 
        dispatch( actions.updateSoldier( id, response.data ) ); 
      } 
      return response;
    }).catch( error => {
      updateNotifcation( toast_id, '', error.message, false );
      return Promise.reject( error );
    });
}

/********************** NON STORE API OPERATIONS **********************/
// load a single soldier - not added to state
export const getSoldier = ( id ) => dispatch => {
  return API.get( `/core/users.php?id=${id}` )
    .then( response => {
      return response.data;
    })
}
// upload a spreadsheet. does not deal with store
export const uploadProfile = ( formData ) => dispatch => {
  const toast_id = createNotifcation('Uploading Profile Picture...');
  return API.post( '/core/users.php?action=uploadProfile', formData )
    .then( response => {
      updateNotifcation( toast_id, 'Image Uploaded!', response.message, response.success)
      return response;
    }).catch( error => { 
      updateNotifcation( toast_id, '', error.message, false );
      return Promise.reject( error );
    })
}
// upload a users spreadsheet
export const uploadSpreadsheet = ( data ) => dispatch => {
  const toast_id = createNotifcation('Uploading user spreadsheet...');
  return API.post( `/upload/users.php`, data )
    .then( response => {
      updateNotifcation( toast_id, 'Image Uploaded!', response.message, response.success);
      return response;
    }).catch( error => {
      toast.dismiss( toast_id );
      console.error( error );
      return Promise.reject( error );
    });
}
