import API from 'api/api';
/**
 * This file handles making the API requests for src/pages/platoons/PlatoonTransitionPage
 * 
 * All api requests map to actions in `/api/core/platoon_transition` of mashpia.com
 * 
 * Note that it does not connect to the redux store as the page is self-contained
 */

export const getUsers = ( data ) => {
  const url = '/core/platoon_transition?action=getUsers';
  return API.post( url, data );
}

export const changePlatoon = ( data ) => {
  const url = '/core/platoon_transition?action=changePlatoon';
  return API.post( url, data );
}

export const removeFromBase = ( user_ids ) => {
  const url = '/core/platoon_transition?action=removeFromBase';
  return API.post( url, { user_ids } );
}

export const transitionPlatoons = () => {
  const url = '/core/platoon_transition?action=transitionPlatoons';
  return API.post( url );
}
