import { combineReducers, createStore, applyMiddleware, compose } from 'redux';
import { LOGOUT, CHANGE_LOGIN } from './login/types';
import thunk from 'redux-thunk';

import login from 'store/login/reducer';
import soldiers from 'store/soldiers/reducer';

export const reducer = combineReducers({
  login, soldiers
})

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
