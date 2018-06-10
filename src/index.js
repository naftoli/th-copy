import React from 'react';
import ReactDOM from 'react-dom';
// Global Styles
import 'bootstrap/dist/css/bootstrap.min.css';
import { Provider } from 'react-redux';
import store from './store';
// Authentication
import checkLogin from 'store/login/checkLogin';
// App
import 'styles/index.css';
import App from './App';

checkLogin( store.dispatch );

ReactDOM.render(
  <Provider store={store}>
    <App />
  </Provider>, 
  document.getElementById('root')
);
