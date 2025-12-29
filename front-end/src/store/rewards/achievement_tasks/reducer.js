import * as types from './types';

export const initialState = {
  loading: false,
  tasks: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_TASKS:
      return { 
        ...state, 
        tasks: action.payload, 
        loading: false
      };

    case types.ADD_TASK:
      return {
        ...state,
        tasks: state.tasks.concat( action.payload )
      };

    case types.UPDATE_TASK:
      return {
        ...state,
        tasks: state.tasks.map(
          task => task.achievement_task_id === action.payload.id ? action.payload.task : task
        )
      };

    default:
      return state; 
  }
}
