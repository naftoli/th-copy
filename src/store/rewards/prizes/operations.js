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

export const getPrize = id => dispatch => {
  return API.get( `/rewards/prizes?id=${id}` )
  .then( handleAPIResponse )
}

export const updatePrize = ( id, updates ) => dispatch => {
  
  return API.post( `/rewards/prizes?id=${id}`, updates )
  .then( handleAPIResponse )
  .then( prize => { 
    dispatch( actions.updatePrize( id, prize ) ); 
    return prize;
  });
}
