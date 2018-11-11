import API from 'api/api';
import * as actions from './actions';

// get all platoons
export const getPlatoons = ( school_id = false, all = false ) => dispatch => {
  // generate query string
  let queries = [];
  if ( school_id ) queries.push( `school_id=${school_id}` );
  if ( all ) queries.push( 'all=true' );
  const query_string = queries.length > 0 ? `?${queries.join('&')}` : '';
  // fetch data
  dispatch( actions.setLoading( true ) );
  return API.get( `/core/platoons${query_string}` )
    .then( platoons => {
      dispatch( actions.setPlatoons( platoons ) );
      return dispatch( actions.setLoading( false ) );
    }).catch( ( error ) => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( error );
    });
}

// get a single platoon
export const getPlatoon = ( id ) => dispatch => {
  return API.get( `/core/platoons?id=${id}` )
}

// update a single soldier
export const updatePlatoon = ( id, data ) => dispatch => {
  return API.post( `/core/platoons?id=${id}`, data )
    .then( platoon => {
      dispatch( actions.updatePlatoon( id, platoon ) );
      return platoon;
    });
}

export const createPlatoon = data => dispatch => {
  return API.post( '/core/platoons', data )
}

export const deletePlatoon = class_id => disptach => {
  return API.delete( `/core/platoons?id=${ class_id }` )
  .then( disptach( actions.deletePlatoon( class_id ) ) )
}

/**
 * this function is used in platoonSelect only
 * TODO: Remove
 */
export const getPlatoonList = ( school_id = false ) => {
  return API.post( `/core/platoons?action=small`, { school_id } )
}