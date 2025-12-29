export const types = {
  SET_LOADING: 'missions/shabbos_mevorchim/set_loading',
  SET_MONTHS: 'missions/shabbos_mevorchim/set_months',
}

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setMonths = months => {
  return {
    type: types.SET_MONTHS,
    payload: months
  }
};
