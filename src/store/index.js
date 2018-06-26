import { combineReducers, createStore, applyMiddleware, compose } from 'redux';
import { LOGOUT } from './login/types';
import thunk from 'redux-thunk';

import login from 'store/login/reducer';
import soldiers from 'store/soldiers/reducer';

export const reducer = combineReducers({
  login, soldiers
})

const rootReducer = ( state, action ) => {
  // reset the state on logout
  if ( action.type === LOGOUT ) { state = undefined; }
  return reducer( state, action );
}

export default createStore( rootReducer, compose(
  applyMiddleware( thunk ),
  window.devToolsExtension ? window.devToolsExtension() : f => f
));
