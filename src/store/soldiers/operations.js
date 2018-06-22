import API from 'api/api';
import * as actions from './actions';

export const getSoldiers = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( '/core/users.php' )
    .then( response => {
      dispatch( actions.setLoading( false ) );
      return dispatch( actions.setSoldiers( response.data ) );
    });
}
