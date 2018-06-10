export const initialState = {
  login: false,
  current_user: false,
  loading: false,
  errors: []
};

export default ( state = initialState, action ) => {
  switch ( action.type ) {
    default: return state; 
  }
}