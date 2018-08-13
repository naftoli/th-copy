import API, { handleAPIResponse } from 'api/api';
/**
 * This file handles making the API requests for src/pages/platoons/PlatoonTransitionPage
 * 
 * All api requests map to actions in `/api/core/platoon_transition` of mashpia.com
 * 
 * Note that it does not connect to the redux store as the page is self-contained
 */

// getUsers in API
export const getUsers = ({ school_id, class_id }) => {
  const url = '/core/platoon_transition?action=getUsers';

  return API.post( url, { school_id, class_id } )
  
  .then( handleAPIResponse )
}