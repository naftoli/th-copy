import { actions, operations } from './actions';
import Cookies from 'universal-cookie';

const cookies = new Cookies();

export default dispatch => {
  const tokens = {
    legacy: cookies.get('admin_auth'),
    mobile: cookies.get('admin')
  }

  if ( tokens.legacy ) {
    dispatch( actions.tokens( tokens.legacy, tokens.mobile ) );
    operations.getCurrentUser()( dispatch );
  }

  return false;
}