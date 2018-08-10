import API, { handleAPIResponse } from 'api/api';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
import * as actions from './actions';

// get all soldiers
export const getPlatoons = ( school_id = false ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  const query_string = school_id ? `?school_id=${school_id}` : '';
  return API.get( `/core/platoons${query_string}` )
    .then( response => {
      dispatch( actions.setPlatoons( response.data ) );
      return dispatch( actions.setLoading( false ) );
    }).catch( ( error ) => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( error );
    });
}

// get a single platoon
export const getPlatoon = ( id ) => dispatch => {
  return API.get( `/core/platoons?id=${id}` ).then( handleAPIResponse );
}

// update a single soldier
export const updatePlatoon = ( id, data ) => dispatch => {
  const toast_id = createNotifcation('Updating Platoon');
  return API.post( `/core/platoons?id=${id}`, data )
    .then( response => {
      updateNotifcation( toast_id, 'Platoon Updated!', response.message, response.success );
      if ( response.success ) dispatch( actions.updatePlatoon( id, response.data ) );
      return response;
    }).catch( error => {
      updateNotifcation( toast_id, '', error.message, false );
      return Promise.reject( error );
    });
}
