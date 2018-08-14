import React from 'react';
import ReactDOM from 'react-dom';
// Global Styles
import 'styles/styles.scss';
// supported languages;
// React-Redux
import { Provider } from 'react-redux';
import store from './store';
// Authentication
import checkLogin from 'store/login/checkLogin';
// Components
import App from './App';

checkLogin( store.dispatch );
// set the langauge to match the browser;

ReactDOM.render(
  <Provider store={store}>
    <App />
  </Provider>, 
  document.getElementById('root')
);
