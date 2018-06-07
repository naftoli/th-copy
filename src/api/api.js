import { Promise } from "core-js";

// import fetch from 'isomorphic-fetch';

// Development url
let API_URL = '//192.168.1.12/api';
// update in production
if ( process.env.NODE_ENV === "production" ) {
  API_URL = '/api'
}
let credentials = process.env.NODE_ENV === "production" ? 'same-origin' : 'include';

/**
 * headers
 * 
 * generate HTTP headers for application.
 */
const headers = () => {
  let headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'mobile': 'false'
  }
  return headers;
}

const parseResponse = response => {
  return response.json();
}

export { API_URL, headers, parseResponse };

export default {
  get( url ) {
    return fetch(`${API_URL}${url}`, {
      method: 'GET',
      headers: headers(),
      credentials: credentials
    }).then( parseResponse );
  },
  
  post( url ) {
    return fetch(`${API_URL}${url}`, {
      method: 'POST',
      headers: headers(),
      credentials: credentials
    }).then( parseResponse )
  },

  delete( url ) {
    return fetch(`${API_URL}${url}`, {
      method: 'DELETE',
      headers: headers(),
      credentials: credentials
    }).then( parseResponse );
  }
}