import API from 'api/api';
import { toast } from 'react-toastify';
import moment from 'moment';
import * as actions from './actions';

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

// load a single soldier - not added to state
export const getSoldier = ( id ) => dispatch => {
  return API.get( `/core/users.php?id=${id}` )
    .then( response => {
      return response.data;
    })
}

// update a single soldier
export const updateSoldier = ( id, data ) => dispatch => {
  const toast_id = toast.info( "Updating Soldier...", { autoClose: false } );
  return API.post( `/core/users.php?id=${id}`, data )
    .then( response => {
      if ( !response.success ) {
        toast.update( toast_id, { type: toast.TYPE.ERROR, render: response.error }); 
      } else { 
        dispatch( actions.updateSoldier( id, response.data ) );
        toast.update( toast_id, { type: toast.TYPE.SUCCESS, render: "Soldier Updated!", autoClose: null });
      };
      return response;
    }).catch( error => {
      toast.update( toast_id, {type: toast.TYPE.ERROR, render: error.error });
      return Promise.reject( error );
    });
}

// upload a users spreadsheet
export const uploadSpreadsheet = ( data ) => dispatch => {
  const toast_id = toast.info( "Uploading user spreadsheet...", { autoClose: false } );
  return API.post( `/upload/users.php`, data )
    .then( response => {
      if ( !response.success ) {
        toast.update( toast_id, { type: toast.TYPE.ERROR, render: response.error });
      } else { 
        toast.update( toast_id, { type: toast.TYPE.SUCCESS, render: "Spreadsheet uploaded and processed!", autoClose: null });
      };
      return response;
    }).catch( error => {
      toast.dismiss( toast_id );
      console.error( error );
      return Promise.reject( error );
    });
}
