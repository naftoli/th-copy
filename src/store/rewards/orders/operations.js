import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getOrders = ( redeemed = false ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/orders?redeemed=${redeemed}` )
  .then( handleAPIResponse )
  .then( orders => { 
    dispatch( actions.setOrders( orders ) );
    return orders;
  });
}
