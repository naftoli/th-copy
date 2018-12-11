
export const types = {
  SET_LOADING: 'missions/subjects/set_loading',
  SET_SUBJECTS: 'missions/subjects/set_subjects',
  SET_LABELS: 'missions/subjects/set_labels',
}

/**
 * setLoading ( loading )
 * 
 * set the state of if we are loading or not
 * 
 * @param {boolean} loading loading state
 */
export const setLoading = ( type, loading ) => {
  return {
    type: types.SET_LOADING,
    payload: { type, loading }
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

/**
 * setLabels( labels )
 * 
 * set the value of the labels reducer
 * 
 * @param {array} labels labels that we are setting
 */
export const setLabels = labels => {
  return {
    type: types.SET_LABELS,
    payload: labels
  }
};
