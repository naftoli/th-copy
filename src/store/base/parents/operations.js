import API from 'api/api';
import * as actions from './actions';

export const getParents = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get(`/core/parents`)
    .then( ({ parents, children }) => {
      dispatch( actions.setParents( parents ) )
      dispatch( actions.setChildren( children ) )
    })
    .catch( error => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( error );
    })
}

export const createParent = ( data ) => dispatch => {
  return API.post( `/core/parents`, data )
}

/***************************** DOES NOT MODIFY STATE. JUST HITS API *****************************/
export const removeChild = ( admin_id, user_id ) => dispatch => {
  return API.post(`/core/parents?action=removeChild`, { admin_id, user_id } )
    .then( () => {
      dispatch( actions.removeChild( admin_id, user_id ) )
    });
}

export const addChild = ( username, user_id ) => dispatch => {
  return API.post(`/core/parents?action=addChild`, { username, user_id } )
    .then( response => dispatch( actions.addChild( response.admin_id, user_id ) ) );
}
