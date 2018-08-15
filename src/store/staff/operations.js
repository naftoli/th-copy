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
