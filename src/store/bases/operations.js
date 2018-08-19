import API, { handleAPIResponse } from 'api/api';
// import { toast } from 'react-toastify';
import * as actions from './actions';

// get all bases
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


//********************** DOES NOT CONNECT TO REDUX **********************/
// get a single base
export const getBase = id => {
  return API.get( `/core/bases?id=${id}` )
  .then( handleAPIResponse );
}
// this function is used in platoonSelect only
export const getBaseList = ( all = false ) => {
  return API.post( `/core/bases?action=small`, { all } )
  .then( handleAPIResponse );
}
