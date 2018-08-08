import API, { handleAPIResponse } from 'api/api';
// import { toast } from 'react-toastify';
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

export const getPlatoon = ( id ) => dispatch => {
  return API.get( `/core/platoons?id=${id}` ).then( handleAPIResponse );
}
