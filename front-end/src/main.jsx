import React from 'react'
import { createRoot } from 'react-dom/client'
import { Provider } from 'react-redux'
import moment from 'moment'
import 'moment/locale/en-ca.js'
import 'moment/locale/en-il.js'
import 'moment/locale/he.js'
import 'moment/locale/fr.js'

import './index.css'
import 'styles/styles.scss'
import App from './App.jsx'
import store from './store'
import checkLogin from 'store/login/checkLogin'


// Set initial state
// Set initial state
console.log('main.jsx: Checking login...');
checkLogin(store.dispatch)
moment.locale(navigator.language)

console.log('main.jsx: Mounting root...');
const root = document.getElementById('root');
if (!root) console.error('main.jsx: ROOT ELEMENT NOT FOUND');

createRoot(root).render(
  <React.StrictMode>
    <Provider store={store}>
      <App />
    </Provider>
  </React.StrictMode>,
)
console.log('main.jsx: Render called');

