export const types = {
  SET_INSTS: `base/institutions/set_insts`,
  SET_LOADING: `base/institutions/set_loading`,
}

//** Action Creators */
export const setLoading = loading => ({
  type: types.SET_LOADING,
  payload: loading
});

export const setInstitutions = institutions => ({
  type: types.SET_INSTS,
  payload: institutions
});
