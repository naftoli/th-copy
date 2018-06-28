import { LEGACY_URL } from 'components/constants';
import Cookies from 'universal-cookie';
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
    'login': `${cookies.get('login')}`
  }
  if ( content_type ) headers['Content-Type'] = content_type;
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