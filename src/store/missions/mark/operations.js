import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getMissions = ( user_id, parsha_id ) => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.post( `/missions/mark?action=getMissions`, { user_id, parsha_id } )
  .then( handleAPIResponse )
  .then( missions => {
    dispatch( actions.setMissions( missions ) );
    return missions;
  });
}
