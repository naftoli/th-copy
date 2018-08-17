import API, { handleAPIResponse } from 'api/api';
// import { toast } from 'react-toastify';
import * as actions from './actions';

// get all soldiers
export const getBases = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/core/bases` )
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
  return API.post( `/core/bases?action=small`, { all } )
  .then( handleAPIResponse );
}
