import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

/********************** PRIZES **********************/

export const getPrizes = () => dispatch => {
  dispatch( actions.setLoading( 'prizes', true ) );
  return API.get( `/rewards/prizes` )
  .then( handleAPIResponse )
  .then( ({ prizes, school_store }) => { 
    dispatch( actions.setPrizes( prizes ) );
    dispatch( actions.setStoreOpen( school_store ) );
    return prizes;
  });
}

export const createPrize = prize => dispatch => {
  return API.post( `/rewards/prizes`, prize )
  .then( handleAPIResponse )
  .then( prize => { 
    dispatch( actions.createPrize( prize ) ); 
    return prize;
  });
}

export const updatePrize = ( id, updates ) => dispatch => {
  return API.post( `/rewards/prizes?id=${id}`, updates )
  .then( handleAPIResponse )
  .then( prize => { 
    dispatch( actions.updatePrize( id, prize ) ); 
    return prize;
  });
}

/********************** PRIZE TEMPLATES **********************/

export const getTemplates = () => dispatch => {
  dispatch( actions.setLoading( 'templates', true ) );
  return API.get( `/rewards/templates` )
  .then( handleAPIResponse )
  .then( templates => { 
    dispatch( actions.setTemplates( templates ) ); 
    return templates;
  });
}

export const createTemplate = template => dispatch => {
  return API.post( `/rewards/templates`, template )
  .then( handleAPIResponse )
  .then( template => { 
    dispatch( actions.createTemplate( template ) ); 
    return template;
  });
}

export const updateTemplate = ( id, updates ) => dispatch => {
  return API.post( `/rewards/templates?id=${id}`, updates )
  .then( handleAPIResponse )
  .then( template => { 
    dispatch( actions.updateTemplate( id, template ) ); 
    return template;
  });
}

/********************** STORE STATUS **********************/
export const setStoreOpen = ( school_store ) => dispatch => {
  return API.post( `/rewards/prizes?action=setStoreOpen`, { school_store } )
  .then( handleAPIResponse )
  .then( data => dispatch( actions.setStoreOpen( data.school_store ) ) );
}

/********************** NON STORE API OPERATIONS **********************/
// upload a prize image. does not deal with store
export const uploadImage = ( formData ) => dispatch => {
  return API.post( '/rewards/prizes?action=uploadImage', formData )
  .then( handleAPIResponse )
}
