import API from 'api/api';
import * as actions from './actions';

/**
 * getTasks
 * 
 * load the tasks with a GET request to /missions/tasks
 */
export const getTasks = () => dispatch => {
  dispatch( actions.setLoading( 'tasks', true ) );

  return API.get( `/missions/tasks` )
    .then( tasks => {
      dispatch( actions.setTasks( tasks ) );
      return tasks
    }).catch( e => {
      dispatch( actions.setLoading( 'tasks', false ) );
      return Promise.reject( e );
    });
}

/**
 * createTask( task )
 * 
 * create a task via POST request to /missions/tasks
 * 
 * @param {object} task the task that we want to add
 */
export const createTask = task => dispatch => {
  dispatch( actions.setLoading( 'saving', true ) );

  return API.post( `/missions/tasks`, task )
    .then( task => {
      dispatch( actions.addTask( task ) );
      return task;
    }).catch( e => {
      dispatch( actions.setLoading( 'saving', false ) );
      return Promise.reject( e );
    });
}

/**
 * updateTask( task )
 * 
 * updates the task via PATCH request to /missions/tasks
 * 
 * @param {object} task the final task including updates
 */
export const updateTask = task => dispatch => {
  dispatch( actions.setLoading( 'updating', true ) );

  return API.patch( `/missions/tasks`, task )
    .then( task => {
      dispatch( actions.updateTask( task ) );
      return task;
    }).catch( e => {
      dispatch( actions.setLoading( 'updating', false ) );
      return Promise.reject( e );
    });
}
