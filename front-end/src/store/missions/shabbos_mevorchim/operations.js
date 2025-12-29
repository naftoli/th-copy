import API from 'api/api';
import * as actions from './actions';

export const getMonths = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/missions/tehillim` )
  .then( months => {
    dispatch( actions.setMonths( months ) );
    return months;
  });
}
