import API from 'api/api';
import { toast } from 'react-toastify';
import * as actions from './actions';

export const getSoldiers = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/core/users.php' )
    .then( response => {
      dispatch( actions.setLoading( false ) );
      return dispatch( actions.setSoldiers( response.data ) );
    }).catch( response => {
      dispatch( actions.setLoading( false ) );
    });
}

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
      toast.update( toast_id, {type: toast.TYPE.ERROR, 
        render: "Network error while updating Soldier. Please check your internet connection."
      });
      console.error( error );
    });
}

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
      toast.update( toast_id, {type: toast.TYPE.ERROR, 
        render: "Network error while uploading. Please check your internet connection."
      });
      console.error( error );
    });
}
