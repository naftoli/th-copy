import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getPrizes = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/prizes` )
  .then( handleAPIResponse )
  .then( prizes => { 
    dispatch( actions.setPrizes( prizes ) ); 
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

export const getTemplates = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/templates` )
  .then( handleAPIResponse )
  .then( templates => { 
    dispatch( actions.setTemplates( templates ) ); 
    return templates;
  });
}

/********************** NON STORE API OPERATIONS **********************/
// upload a prize image. does not deal with store
export const uploadImage = ( formData ) => dispatch => {
  return API.post( '/rewards/prizes?action=uploadImage', formData )
  .then( handleAPIResponse )
}
