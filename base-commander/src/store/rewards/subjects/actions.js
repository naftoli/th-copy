
export const types = {
  SET_LOADING: 'missions/subjects/set_loading',
  SET_SUBJECTS: 'missions/subjects/set_subjects',
}

/**
 * setLoading ( loading )
 * 
 * set the state of if we are loading or not
 * 
 * @param {boolean} loading loading state
 */
export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

/**
 * setSubjects( subjects )
 * 
 * set the value of the subjects reducer
 * 
 * @param {array} subjects subjects that we are setting
 */
export const setSubjects = subjects => {
  return {
    type: types.SET_SUBJECTS,
    payload: subjects
  }
};
