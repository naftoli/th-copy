import API from 'api/api';
// import { toast } from 'react-toastify';
import * as actions from './actions';

// get all soldiers
export const getPlatoons = ( id ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  const query_string = id ? `?school_id=${id}` : '';
  return API.get( `/core/platoons.php${query_string}` )
    .then( response => {
      dispatch( actions.setLoading( false ) );
      return dispatch( actions.setPlatoons( response.data ) );
    }).catch( () => {
      dispatch( actions.setLoading( false ) );
    });
}
// update a single soldier
export const updatePlatoon = ( id, data ) => dispatch => {
  // const toast_id = toast.info( "Updating Platoon...", { autoClose: false } );
  // return API.post( `/core/platoons.php?id=${id}`, data )
  //   .then( response => {
  //     if ( !response.success ) {
  //       toast.update( toast_id, { type: toast.TYPE.ERROR, render: response.error }); 
  //     } else { 
  //       dispatch( actions.updateSoldier( id, response.data ) );
  //       toast.update( toast_id, { type: toast.TYPE.SUCCESS, render: "Soldier Updated!", autoClose: null });
  //     };
  //     return response;
  //   }).catch( error => {
  //     toast.update( toast_id, {type: toast.TYPE.ERROR, render: error.message });
  //     return Promise.reject( error );
  //   });
}

/********************** NON STORE API OPERATIONS **********************/
// load a single soldier - not added to state
export const getPlatoon = ( id ) => dispatch => {
  // return API.get( `/core/users.php?id=${id}` )
  //   .then( response => {
  //     return response.data;
  //   })
}
