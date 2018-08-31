import * as types from './types';

export const initialState = {
  loading: false,
  tasks: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { ...state, loading: action.payload };

    case types.SET_TASKS:
      return { ...state, tasks: action.payload, loading: false };

    case types.ADD_TASK:
      debugger;
      return state;

    case types.UPDATE_TASK:
      debugger;
      return state;

    default:
      return state; 
  }
}
