
export const types = {
  UPDATE_TASK: 'missions/tasks/update_task',
  SET_LOADING: 'missions/tasks/set_loading',
  SET_TASKS: 'missions/tasks/set_tasks',
  ADD_TASK: 'missions/tasks/add_task',
}

/**
 * setLoading ( loading )
 * 
 * set the state of if we are loading or not
 * 
 * @param {boolean} loading loading state
 */
export const setLoading = ( type, loading ) => {
  return {
    type: types.SET_LOADING,
    payload: { type, loading }
  }
};

/**
 * setTasks( tasks )
 * 
 * set the value of the tasks reducer
 * 
 * @param {array} tasks tasks that we are setting
 */
export const setTasks = tasks => {
  return {
    type: types.SET_TASKS,
    payload: tasks
  }
};

/**
 * addTask( task )
 * 
 * add a task to the reducer
 * 
 * @param {array} task task that we are setting
 */
export const addTask = task => {
  return {
    type: types.ADD_TASK,
    payload: task
  }
};

/**
 * updateTask( task )
 * 
 * update a single task based on the grid_id and the lang id
 * 
 * @param {array} task task that we are setting
 */
export const updateTask = task => {
  return {
    type: types.UPDATE_TASK,
    payload: task
  }
};
