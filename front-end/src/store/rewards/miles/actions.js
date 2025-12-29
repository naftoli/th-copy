// types
export const types = {
  SET_MILES: 'rewards/miles/set_miles'
}

export const setMiles = miles => {
  return {
    type: types.SET_MILES,
    payload: miles
  }
};
