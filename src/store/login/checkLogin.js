import { setTokens } from './actions';
import { getCurrentUser } from './operations';
import Cookies from 'universal-cookie';

const cookies = new Cookies();

export default dispatch => {
  const tokens = {
    legacy: cookies.get('admin_auth'),
    mobile: cookies.get('admin'),
    id:     cookies.get('admin_id'),
  }

  if ( tokens.legacy ) {
    dispatch( setTokens( tokens.legacy, tokens.mobile, tokens.id ) );
    getCurrentUser()( dispatch );
  }

  return false;
}