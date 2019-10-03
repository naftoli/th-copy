import { types } from './actions';

const initialState = {
  tasks: [],
  loading: {}
};

export default ( state = initialState, action ) => {

  const { type, payload } = action;

  switch ( type ) {

    case types.SET_LOADING:
      return { ...state, 
        loading: {
          ...state.loading,
          [ payload.type ]: payload.loading
        }
      };
    
    case types.SET_TASKS:
      return {
        ...state,
        tasks: payload,
        loading: {
          ...state.loading,
          tasks: false
        }
      };

    case types.ADD_TASK:
      return {
        ...state,
        tasks: [ payload, ...state.tasks ],
        loading: {
          ...state.loading,
          saving: false
        }
      };

    case types.UPDATE_TASK:
      return {
        ...state,
        
        tasks: state.tasks.map( task => {
          if ( payload.grid_id === task.grid_id && payload.lang_id === task.lang_id )
            return payload;
            
          return task;
        }),

        loading: {
          ...state.loading,
          updating: false
        },
      };

    default:
      return state;
  }
}
