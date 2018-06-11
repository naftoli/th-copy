import API from 'api/api';

//** Types */
export const types = {
  SET_LOADING: `login/set_loading`,
  SET_ERRORS: `login/set_errors`,
  SET_TOKENS: `login/set_tokens`,
  SET_USER: `login/set_user`,
  LOGOUT: `login/logout`
};

//** Action Creators */
export const actions = {

  loading: loading => {
    return {
      type: types.SET_LOADING,
      payload: loading
    }
  },

  tokens: ( legacy, mobile ) => {
    return {
      type: types.SET_TOKENS,
      payload: { legacy, mobile }
    }
  },

  setErrors: errors => {
    // convert single emements to an array
    errors = Array.isArray( errors ) ? errors : [ errors ];
    return {
      type: types.SET_ERRORS,
      payload: errors
    }
  },

  setUser: user => {
    return {
      type: types.SET_USER,
      payload: user
    }
  },

  logout: () => ({
    type: types.LOGOUT
  })
}

export const operations = {
  login: ( username, password ) => {
    return dispatch => {
      dispatch( actions.loading( true ) );
      return API.post('/auth/login.php', { username, password })
        .then( response => {
          dispatch( actions.loading( false ) );
          if ( response.success ) {
            dispatch( actions.tokens( response.data.legacy, response.data.mobile ));
            dispatch( actions.setUser( response.data.user ));
          } else {
            dispatch( actions.setErrors( response.error ) );
          }
        })
        .catch( error => {
          dispatch( actions.loading( false ) );
          dispatch( actions.setErrors( "Network Error" ));
        });
    }
  }
}