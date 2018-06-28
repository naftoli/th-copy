import { setTokens, changeLogin } from './actions';
import { getCurrentUser } from './operations';
import Cookies from 'universal-cookie';

const cookies = new Cookies();

export default dispatch => {
  const tokens = {
    legacy: cookies.get('admin_auth'),
    mobile: cookies.get('admin'),
    id:     cookies.get('admin_id'),
  }
  // if we have tokens
  if ( tokens.legacy ) {
    dispatch( setTokens( tokens.legacy, tokens.mobile, tokens.id ) );
    getCurrentUser()( dispatch ).then( () => {
      // change to the selected login if we have one...
      if ( cookies.get( 'login' ) ) {
        const [ type, id ] = cookies.get( 'login' ).split('-');
        dispatch( changeLogin( type, parseInt(id, 10) ) );
      }
    });
  }

  return false;
}