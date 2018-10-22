import API, { handleAPIResponse } from 'api/api';
import * as actions from './actions';

export const getGrid = ( type, date ) => dispatch => {
  dispatch( actions.setLoading( type, true ) );

  return API.post( `/missions/grid?action=get`, { type, date } )
  .then( handleAPIResponse )
  .then( ({ missions, soldiers}) => {
    dispatch( actions.setSoldiers( type, soldiers ) );
    dispatch( actions.setMissions( type, missions ) );
  })
  .catch( e => {
    dispatch( actions.setLoading( false ))
    return Promise.reject( e );
  });
}

export const markTask = ( type, user_ids, grid_id, date, mark ) => dispatch => {
  let data = { user_ids, grid_id, dates: [ date ], mark };

  dispatch( actions.markTask( type, user_ids, grid_id, date, mark ) );

  return API.post( `/missions/mark`, data )
  .then( handleAPIResponse );
}
