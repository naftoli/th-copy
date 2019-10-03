import { combineReducers, createStore, applyMiddleware, compose } from 'redux';
import { types } from './login/actions';
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
  if ( action.type === types.LOGOUT )
    state = undefined;
  // reset all non-login state when login is changed
  if ( action.type === types.CHANGE_LOGIN )
    state = Object.assign({}, { login: state.login });
  // return the reducer
  return reducer( state, action );
}

let extension = f => f;
// * only connect to devtools in development
if ( window.__REDUX_DEVTOOLS_EXTENSION__ && process.env.NODE_ENV !== 'production' ) {
  extension = window.__REDUX_DEVTOOLS_EXTENSION__();
}

export default createStore( rootReducer, compose(
  applyMiddleware( thunk ), extension
));
