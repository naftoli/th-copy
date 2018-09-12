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

export const processOrders = ( action, user_prize_ids ) => dispatch => {
  return API.post( `/rewards/orders?action=${action}`, { user_prize_ids } )
}
