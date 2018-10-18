import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';
import { toast } from 'react-toastify';

export const getMissions = ( user_id, parsha_id ) => dispatch => {
  dispatch( actions.setLoading( true ) );

  return API.post( `/missions/mark?action=get`, { user_id, parsha_id } )
  .then( handleAPIResponse )
  .then( missions => {
    dispatch( actions.setMissions( missions ) );
    return missions;
  });
}

export const markMission = ( user_ids, grid_id, dates, mark ) => dispatch => {
  let data = { user_ids, grid_id, dates, mark };

  return API.post( `/missions/mark`, data )
  .then( handleAPIResponse )
  .catch( e => toast.error( e.message ) )
  .then( () => dispatch(
    actions.markMission( grid_id, dates, mark )
  ) );
}
