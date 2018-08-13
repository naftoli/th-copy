import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getParents = () => dispatch => {
  return API.get(`/core/parents`)
  .then( handleAPIResponse )
  .then( ({ parents, children }) => {
    dispatch( actions.setParents( parents ) )
    dispatch( actions.setChildren( children ) )
  });
}

/***************************** DOES NOT MODIFY STATE. JUST HITS API *****************************/
export const removeChild = ( admin_id, user_id ) => dispatch => {
  return API.post(`/core/parents?action=removeChild`, { admin_id, user_id } )
    .then( handleAPIResponse);
}

export const addChild = ( username, user_id ) => dispatch => {
  return API.post(`/core/parents?action=addChild`, { username, user_id } )
    .then( handleAPIResponse);
}
