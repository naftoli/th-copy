import { combineReducers, createStore, applyMiddleware, compose } from 'redux';
import thunk from 'redux-thunk';
import loginReducer from 'store/login/reducer';

export const reducer = combineReducers({
  login: loginReducer
})

export default createStore( reducer, compose(
  applyMiddleware( thunk ),
  window.devToolsExtension ? window.devToolsExtension() : f => f
));
