import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getStaff = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get(`/core/staff`)
  .then( handleAPIResponse )
  .then( staff => dispatch( actions.setStaff( staff ) ) )
  .catch( error => {
    dispatch( actions.setLoading( false ) );
    return Promise.reject( error );
  })
}

export const createStaff = ( data ) => dispatch => {
  return API.post( `/core/staff`, data )
  .then( handleAPIResponse )
}

export const updateStaff = ( admin_id, updates ) => dispatch => {
  return API.post( `/core/staff?id=${admin_id}`, updates )
  .then( handleAPIResponse )
  .then( response => dispatch( actions.updateStaff( admin_id, response ) ) );
}

/************************* ADMIN AUTHS *************************/
export const removeAuth = ( auth ) => dispatch =>  {
  return API.delete( `/core/admin_auths`, auth )
  .then( handleAPIResponse )
  .then( auth => dispatch( actions.removeAuth( auth ) ) );
}

export const createAuth = ( auth ) => dispatch =>  {
  return API.post( `/core/admin_auths`, auth )
  .then( handleAPIResponse )
  .then( response => dispatch( actions.createAuth( response ) ) );
}

/************************* THIS FUNCTION DOES NOT CONNECT TO REDUX YET *************************/
/** updateAuth updates the admin_auth relevent to the staff position. 
 * Does not update the store in redux yet as the api does not return the final position yet. Just the one for this position.
 */
export const updateAuth = ( auth ) => {
  return API.patch( `/core/admin_auths`, auth )
  .then( handleAPIResponse )
}
