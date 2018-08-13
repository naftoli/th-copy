import API, { handleAPIResponse } from 'api/api';
// import { toast } from 'react-toastify';
import * as actions from './actions';

// get all soldiers
export const getBases = ( all = false ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  all = all ? '?all=true' : '';
  return API.get( `/core/bases.php${all}` )
    .then( response => {
      dispatch( actions.setBases( response.data ) );
      return dispatch( actions.setLoading( false ) );
    }).catch( () => {
      dispatch( actions.setLoading( false ) );
    });
}

/**
 * this function is used in platoonSelect only
 */
export const getBaseList = ( all = false ) => {
  const query_string = all ? '?all=true' : '';

  return API.get( `/core/bases${query_string}` )
  .then( handleAPIResponse );
}
