import API from 'api/api';
import * as actions from './actions';

export const getGrid = ( type, date ) => dispatch => {
  dispatch( actions.setLoading( type, true ) );

  return API.post( `/missions/grid?action=get`, { type, date } )
  .then( ({ missions, soldiers}) => {
    dispatch( actions.setSoldiers( type, soldiers ) );
    dispatch( actions.setMissions( type, missions ) );
  })
  .catch( e => {
    dispatch( actions.setLoading( type, false ))
    return Promise.reject( e );
  });
}

export const markTask = ( type, user_ids, grid_id, date, mark ) => dispatch => {
  let data = { user_ids, grid_id, dates: [ date ], mark };

  dispatch( actions.markTask( type, user_ids, grid_id, date, mark ) );

  if ( data.user_ids.length > 0 )
    return API.post( `/missions/mark`, data )
;
  return Promise.resolve();
}

export const customizeGrid = ( type, missions ) => dispatch => {
  // * update the UI
  dispatch( actions.setMissions( type, missions ) );
  // * save the way the missions where sorted
  const mission_sort = missions.map( mission => mission.grid_id );
  // * sort missions into enabled and disabled
  const mission_status = missions.reduce(
    ( res, mission ) => {
      const key = mission.disabled ? 'disabled' : 'enabled';
      res[ key ] = [ ...res[ key ], mission.grid_id ];
      return res
    }, 
    { enabled: [], disabled: [] }
  );
  // prepare for server
  const data = { type, mission_sort, mission_status };
  // * save the changes
  return API.post( `/missions/grid?action=customize`, data );
}
