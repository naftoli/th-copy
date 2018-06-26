import { combineReducers, createStore, applyMiddleware, compose } from 'redux';
import { LOGOUT } from './login/types';
import thunk from 'redux-thunk';

import loginReducer from 'store/login/reducer';
import soldierReducer from 'store/soldiers/reducer';
import errorReducer from 'store/errors/reducer';

export const reducer = combineReducers({
  login: loginReducer,
  soldiers: soldierReducer,
  errors: errorReducer
})

const rootReducer = ( state, action ) => {
  // reset the state on logout
  if ( action.type === LOGOUT ) {
    state = undefined;
  }

  return reducer( state, action );
}

export default createStore( rootReducer, compose(
  applyMiddleware( thunk ),
  window.devToolsExtension ? window.devToolsExtension() : f => f
));
