import { combineReducers, createStore, applyMiddleware, compose } from 'redux';
import { LOGOUT, CHANGE_LOGIN } from './login/types';
import thunk from 'redux-thunk';

import home from 'store/home/reducer';
import login from 'store/login/reducer';
import payments from 'store/payments/reducer';

// nested reducers
import base from './base';
import rewards from './rewards';
import missions from './missions';


export const reducer = combineReducers({
  // nested
  rewards,  base,   missions,
  // flat
  payments, login,  home,
});

const rootReducer = ( state, action ) => {
  // reset the state on logout
  if ( action.type === LOGOUT ) { state = undefined; }
  // reset all non-login state when login is changed
  if ( action.type === CHANGE_LOGIN ) { state = Object.assign({}, { login: state.login }); }

  return reducer( state, action );
}

export default createStore( rootReducer, compose(
  applyMiddleware( thunk ),
  window.devToolsExtension ? window.devToolsExtension() : f => f
));
