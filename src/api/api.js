import { LEGACY_URL } from 'components/constants';
import Cookies from 'universal-cookie';
import { Promise } from 'core-js';
import striptags from 'striptags';

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
  return response.text()
  .then( text => {
    try {
      return JSON.parse( text );
    // catch json parsing errors
    } catch ( e ) {
      text = striptags( text );
      // if there is no text in the response
      if ( !text || text.length === 0 )
        text = response.status + ' Server Error';
      // reject with the correct error
      return Promise.reject( new Error( text ) );
    }
  });
}

const parseResponse = response => {
  if ( response.success === false && response.message )
    return Promise.reject( response );
  if ( response.success === false )
    return Promise.reject( new Error('Unknown Server Error') );
  return response.data;
}
// export for testing
export { API_URL, headers, toJSON, parseResponse };

export default {
  get( url ) {
    return fetch(`${API_URL}${url}`, {
      method: 'GET',
      headers: headers(),
      cache: 'no-store',
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
      cache: 'no-store',
      body
    }).then( toJSON ) // convert to json
      .then( parseResponse ) // understand the response
  },

  patch( url, data = {}, content_type = defaultContentType ) {
    // support FormData ( for things like images )
    const body = data instanceof FormData ? data : JSON.stringify(data);
    content_type = data instanceof FormData ? false : content_type;
    // make the request and parse the response
    return fetch(`${API_URL}${url}`, {
      method: 'PATCH',
      headers: headers( content_type ),
      credentials: credentials,
      cache: 'no-store',
      body
    }).then( toJSON ) // convert to json
      .then( parseResponse ) // understand the response
  },

  delete( url, data = {} ) {
    const body = data instanceof FormData ? data : JSON.stringify(data);
    return fetch(`${API_URL}${url}`, {
      method: 'DELETE',
      headers: headers(),
      credentials: credentials,
      cache: 'no-store',
      body
    }).then( toJSON ) // convert to json
      .then( parseResponse ) // understand the response
  }
}