import API from 'api/api';
import * as actions from './actions';

// get all tasks
export const getTasks = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/achievement_tasks` )
    .then( tasks => {
      dispatch( actions.setTasks( tasks ) );
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}

export const updateTask = ( id, data ) => dispatch => {
  return API.post( `/rewards/achievement_tasks?id=${id}`, data )
  .then( task => { 
    dispatch( actions.updateTask( id, task ) ); 
    return task;
  });
}

export const createTask = data => dispatch => {
  return API.post( `/rewards/achievement_tasks`, data )
  .then( task => { 
    dispatch( actions.addTask( task ) ); 
    return task;
  });
}
