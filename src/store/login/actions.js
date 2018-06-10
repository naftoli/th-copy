import API from 'api/api';

//** Types */
export const types = {
  SET_LOADING: `login/set_loading`,
  SET_ERRORS: `login/set_errors`,
  SET_TOKENS: `login/set_tokens`,
  SET_USER: `login/set_user`
};

//** Action Creators */
export const actions = {

  loading: loading => {
    return {
      action: types.SET_LOADING,
      payload: { loading }
    }
  },

  setErrors: errors => {
    // convert single emements to an array
    errors = Array.isArray( errors ) ? errors : [ errors ];
    return {
      action: types.SET_ERRORS,
      payload: { errors }
    }
  },

  tokens: ( legacy, mobile ) => {
    return {
      action: types.SET_TOKENS,
      payload: { legacy, mobile }
    }
  },

  setUser: user => {
    return {
      action: types.SET_USER,
      payload: { user }
    }
  }
}

export const operations = {
  login: ( username, password ) => {
    return dispatch => {
      dispatch( actions.loading( true ) );
      API.post('/auth/login.php', { username, password })
        .then( response => {

        })
        .catch( error => {
          dispatch( actions.loading( false ) );
          dispatch( actions.setErrors( "Network Error" ));
        });
    }
  }
}