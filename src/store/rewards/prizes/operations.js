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
