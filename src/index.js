import React from 'react';
import ReactDOM from 'react-dom';
// Global Styles
import 'styles/styles.scss';
// supported languages;
import moment from 'moment';
import 'moment/locale/en-ca.js';
import 'moment/locale/he.js';
import 'moment/locale/fr.js';
// React-Redux
import { Provider } from 'react-redux';
import store from './store';
// Authentication
import checkLogin from 'store/login/checkLogin';
// Components
import App from './App';

checkLogin( store.dispatch );
// set the langauge to match the browser;
moment.locale( navigator.language );

ReactDOM.render(
  <Provider store={store}>
    <App />
  </Provider>, 
  document.getElementById('root')
);
