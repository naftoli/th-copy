import { types } from './actions';

export const initialState = {
  loading: false,
  missions: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state, 
        loading: action.payload 
      };

    case types.SET_MISSIONS:
      return { 
        ...state, 
        missions: action.payload, 
        loading: false
      };

    case types.MARK_MISSON:
      return {
        ...state,
        missions: state.missions.map( mission => {
          // now that we are in a new scope, create some variables.
          let { date_task_marks, frequency_name, date_task_id, quantity } = mission;
          // do not touch other missions
          if ( date_task_id !== action.payload.date_task_id )
            return mission;
          // update daily mission marks
          if ( frequency_name === "Daily" ) {
            mission.date_task_marks = date_task_marks.map( mark => {
              if ( action.payload.dates.includes( mark.mark_date ) )
                mark.marked = action.payload.mark;
              return mark;
            }); // end updating marks
          // weekly missions
          } else if ( quantity ) {
            mission.date_task_mark.marked = !!action.payload.mark;
            mission.date_task_mark.done_qty = action.payload.mark;
          } else
            mission.date_task_mark.marked = action.payload.mark;

          return mission;
        }) // end updating missions
      }

    default:
      return state; 
  }
}
