import API from 'api/api';

/***************************** DOES NOT MODIFY STATE. JUST HITS API *****************************/
export const removeChild = ( admin_id, user_id ) => dispatch => {
  return API.post(`/core/parents?action=removeChild`, { admin_id, user_id } )
    .then( response => {
      if ( response.success ) return response;
      return Promise.reject( response );
    });
}

export const addChild = ( username, user_id ) => dispatch => {
  return API.post(`/core/parents?action=addChild`, { username, user_id } )
    .then( response => {
      if ( response.success ) return response;
      return Promise.reject( response );
    });
}
