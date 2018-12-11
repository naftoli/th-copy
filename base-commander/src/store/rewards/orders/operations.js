import API from 'api/api';
import * as actions from './actions';

export const getOrders = ( redeemed = false ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/orders?redeemed=${redeemed}` )
  .then( orders => { 
    dispatch( actions.setOrders( orders ) );
    return orders;
  });
}

export const processOrders = ( action, user_prize_ids ) => dispatch => {
  return API.post( `/rewards/orders?action=${action}`, { user_prize_ids } )
}

export const getStore = user_id => dispatch => {
  dispatch( actions.setStore( false ) )
  return API.post( `/rewards/orders?action=store`, { user_id } )
  .then( store => dispatch( actions.setStore( store ) ) )
}

export const placeOrder = order => dispatch => {
  return API.post( `/rewards/orders?action=order`, order )
  .then( order => dispatch( actions.addOrder( order ) ) )
}
