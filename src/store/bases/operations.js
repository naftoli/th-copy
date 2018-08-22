import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';
// functions
import { createNotifcation, updateNotifcation } from 'functions/notifications';

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

// get a single base
export const getBase = id => dispatch => {
  return API.get( `/core/bases?id=${id}` )
  .then( handleAPIResponse );
}

// update a single base
export const updateBase = ( id, data ) => dispatch => {
  const toast_id = createNotifcation( 'Updating Base' );
  return API.post( `/core/bases?id=${id}`, data )
  .then( response => { 
    updateNotifcation( toast_id, 'Base Updated!', response.message, response.success );
    dispatch( actions.updateBase( id, response.data ) ); 
    return response.data;
  })
  .catch( error => {
    updateNotifcation( toast_id, '', error.message, false );
    return Promise.reject( error );
  });
}

//********************** DOES NOT CONNECT TO REDUX **********************/

// this function is used in platoonSelect only
export const getBaseList = ( all = false ) => {
  return API.post( `/core/bases?action=small`, { all } )
  .then( handleAPIResponse );
}
