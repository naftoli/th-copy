import { types } from './actions';

export const initialState = {
  daily: {
    loading: false, missions: [], soldiers: [],
  },
  weekly: {
    loading: false, missions: [], soldiers: [],
  },
  tehillim: {
    loading: false, missions: [], soldiers: [],
  }
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    
    case types.SET_LOADING:
      return { 
        ...state,
        [ action.payload.type ]: {
          ...state[action.payload.type],
          loading: action.payload.loading
        }
      };

    case types.SET_MISSIONS:
      return { 
        ...state,
        [ action.payload.type ]: {
          ...state[action.payload.type],
          missions: action.payload.missions,
          loading: false
        }
      };

    case types.SET_SOLDIERS:
      return { 
        ...state,
        [ action.payload.type ]: {
          ...state[action.payload.type],
          soldiers: action.payload.soldiers,
          loading: false
        }
      };

    case types.MARK_TASK:
      return {
        ...state,
        // update just that part of the state
        [ action.payload.type ]: {
          ...state[action.payload.type],
          soldiers: state[action.payload.type].soldiers.map( soldier => {
            // now that we are in a new scope, create some variables.
            let { user_ids, grid_id, mark, date } = action.payload;
            // do not touch soldiers we are not updating
            if ( !user_ids.includes( soldier.user_id ) )
              return soldier;
            // remove any existing marks from the soldier
            if ( soldier.marks[ grid_id ] )
              delete soldier.marks[ grid_id ];
            // if we have a mark, add the mark back to the soldier
            if ( mark > 0 )
              soldier.marks[ grid_id ] = {
                mark_date: date,
                done_qty: mark
              }

            return soldier;
          }) // end updating missions
        }
      }

    default:
      return state; 
  }
}
