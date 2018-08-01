import { LEGACY_URL } from 'components/constants';
import Cookies from 'universal-cookie';
import { toast } from 'react-toastify';
import { Promise } from 'core-js';
const cookies = new Cookies();
// API url
let API_URL = `${LEGACY_URL}/api`;
// send cookies
const credentials = process.env.NODE_ENV === "production" ? 'same-origin' : 'include';
const defaultContentType = 'application/json; charset=utf-8';
/**
 * headers
 * 
 * generate HTTP headers for application.
 */
const headers = ( content_type = defaultContentType ) => {
  let headers = {
    'Accept': 'application/json',
    'Authorization': `Legacy ${cookies.get('admin_id')}-${cookies.get('admin_auth')}`,
  }
  if ( cookies.get('login') ) headers['login'] = cookies.get('login');
  if ( content_type ) headers['Content-Type'] = content_type;
  return headers;
}

const toJSON = response => {
  return response.json();
}

const parseResponse = response => {
  if ( response.success === false && response.message ) {
    return Promise.reject( response );
  }
  return response;
}

export { API_URL, headers, toJSON, parseResponse };

export default {
  get( url ) {
    return fetch(`${API_URL}${url}`, {
      method: 'GET',
      headers: headers(),
      credentials: credentials
    }).then( toJSON ) // convert to json
      .then( parseResponse ) // understand the response
  },
  
  post( url, data = {}, content_type = defaultContentType ) {
    // support FormData ( for things like images )
    const body = data instanceof FormData ? data : JSON.stringify(data);
    content_type = data instanceof FormData ? false : content_type;
    // make the request and parse the response
    return fetch(`${API_URL}${url}`, {
      method: 'POST',
      headers: headers( content_type ),
      credentials: credentials,
      body
    }).then( toJSON ) // convert to json
      .then( parseResponse ) // understand the response
  },

  delete( url ) {
    return fetch(`${API_URL}${url}`, {
      method: 'DELETE',
      headers: headers(),
      credentials: credentials
    }).then( toJSON ) // convert to json
      .then( parseResponse ) // understand the response
  }
}