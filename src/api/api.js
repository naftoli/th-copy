import { LEGACY_URL } from 'components/constants';
import Cookies from 'universal-cookie';
const cookies = new Cookies();
// API url
let API_URL = `${LEGACY_URL}/api`;
// send cookies
const credentials = process.env.NODE_ENV === "production" ? 'same-origin' : 'include';
/**
 * headers
 * 
 * generate HTTP headers for application.
 */
const headers = ( content_type ) => {
  let headers = {
    'Accept': 'application/json',
    'Content-Type': content_type || 'application/json; charset=utf-8',
    'mobile': 'false',
    'Authorization': `Legacy ${cookies.get('admin_id')}-${cookies.get('admin_auth')}`
  }
  return headers;
}

const parseResponse = response => {
  return response.json();
}

export { API_URL, headers, parseResponse };

export default {
  get( url, content_type = false ) {
    return fetch(`${API_URL}${url}`, {
      method: 'GET',
      headers: headers( content_type ),
      credentials: credentials
    }).then( parseResponse );
  },
  
  post( url, data = {}, content_type = false ) {
    const body = JSON.stringify(data);
    return fetch(`${API_URL}${url}`, {
      method: 'POST',
      headers: headers( content_type ),
      credentials: credentials,
      body
    }).then( parseResponse )
  },

  delete( url, content_type = false ) {
    return fetch(`${API_URL}${url}`, {
      method: 'DELETE',
      headers: headers( content_type ),
      credentials: credentials
    }).then( parseResponse );
  }
}