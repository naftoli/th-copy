import { LEGACY_URL } from 'components/constants';
// Development url
let API_URL = `${LEGACY_URL}/api`;
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
  
  post( url, data = {} ) {
    const body = JSON.stringify(data);
    return fetch(`${API_URL}${url}`, {
      method: 'POST',
      headers: headers(),
      credentials: credentials,
      body
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