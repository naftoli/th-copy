export const types = {
  SET_LOADING: 'missions/parshos/set_loading',
  SET_PARSHOS: 'missions/parshos/set_parshos',
}

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setParshos = parshos => {
  return {
    type: types.SET_PARSHOS,
    payload: parshos
  }
};
