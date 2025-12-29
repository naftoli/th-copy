import API from 'api/api';
import * as actions from './actions';

export const getParshos = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/missions/parshos` )
  .then( parshos => {
    dispatch( actions.setParshos( parshos ) );
    return parshos;
  });
}
