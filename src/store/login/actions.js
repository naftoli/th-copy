import { 
  SET_LOADING, SET_ERRORS, SET_TOKENS, 
  SET_USER, LOGOUT, CHANGE_LOGIN 
} from './types';

//** Action Creators */
export const setLoading = loading => {
  return {
    type: SET_LOADING,
    payload: loading
  }
};

export const setTokens = ( legacy, mobile, id ) => {
  return {
    type: SET_TOKENS,
    payload: { legacy, mobile, id }
  }
};

export const setErrors = errors => {
  // convert single emements to an array
  errors = Array.isArray( errors ) ? errors : [ errors ];
  return {
    type: SET_ERRORS,
    payload: errors
  }
};

export const setUser = user => {
  return {
    type: SET_USER,
    payload: user
  }
};

export const logout = () => ({ 
  type: LOGOUT 
});

export const changeLogin = ( type, id ) => {
  return {
    type: CHANGE_LOGIN,
    payload: { type, id }
  }
}
