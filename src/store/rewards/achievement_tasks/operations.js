import API from 'api/api';
import * as actions from './actions';

// get all tasks
export const getTasks = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/achievement_tasks` )
    .then( response => {
      dispatch( actions.setTasks( response.data ) );
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}

export const updateTask = ( id, data ) => dispatch => {
  return API.post( `/rewards/achievement_tasks?id=${id}`, data )
  .then( ({ data }) => { 
    dispatch( actions.updateTask( id, data ) ); 
    return data;
  });
}

export const createTask = ( data ) => dispatch => {
  return API.post( `/rewards/achievement_tasks`, data )
  .then( ({ data }) => { 
    dispatch( actions.addTask( data ) ); 
    return data;
  });
}
