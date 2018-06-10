import { actions } from './actions';
import Cookies from 'universal-cookie';

const cookies = new Cookies();

export default dispatch => {
  const tokens = {
    legacy: cookies.get('admin_auth'),
    mobile: cookies.get('admin')
  }

  if ( tokens.legacy )
    return dispatch( actions.tokens( tokens.legacy, tokens.mobile ) );

  return false;
}