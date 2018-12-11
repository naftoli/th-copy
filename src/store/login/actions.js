export const types = {
  CHANGE_LOGIN: `login/change_login`,
  SET_LOADING:  `login/set_loading`,
  UPDATE_USER:  `login/update_user`,
  SET_ERRORS:   `login/set_errors`,
  SET_TOKENS:   `login/set_tokens`,
  SET_TITLE:    `login/set_title`,
  SET_USER:     `login/set_user`,
  LOGOUT:       `login/logout`,
}

//** Action Creators */
export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setTokens = ( legacy, mobile, id ) => {
  return {
    type: types.SET_TOKENS,
    payload: { legacy, mobile, id }
  }
};

export const setErrors = errors => {
  // convert single emements to an array
  errors = Array.isArray( errors ) ? errors : [ errors ];
  return {
    type: types.SET_ERRORS,
    payload: errors
  }
};

export const setUser = user => ({
  type: types.SET_USER,
  payload: user
});

export const updateUser = updates => ({
  type: types.UPDATE_USER,
  payload: updates
})

export const logout = () => ({ 
  type: types.LOGOUT 
});

export const changeLogin = ( type, id ) => ({
  type: types.CHANGE_LOGIN,
  payload: { type, id }
});

export const setTitle = title => ({
  type: types.SET_TITLE,
  payload: title
});
