import { types } from './actions';

const initialState = {
  labels: [],
  loading: {},
  subjects: [],
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

    case types.SET_SUBJECTS:
      return {
        ...state,
        subjects: payload,
        loading: {
          ...state.loading,
          subjects: false
        }
      };

    case types.SET_LABELS:
      return {
        ...state,
        labels: payload,
        loading: {
          ...state.loading,
          labels: false
        }
      };

    default:
      return state;
  }
}
