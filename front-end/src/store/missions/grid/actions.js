export const types = {
  MARK_TASK: 'missions/grid/mark_task',
  SET_LOADING: 'missions/grid/set_loading',
  SET_MISSIONS: 'missions/grid/set_missions',
  SET_SOLDIERS: 'missions/grid/set_soldiers',
}

export const setLoading = ( type, loading ) => {
  return {
    type: types.SET_LOADING,
    payload: { type, loading }
  }
};

export const setMissions = ( type, missions ) => {
  return {
    type: types.SET_MISSIONS,
    payload: { type, missions }
  }
};

export const setSoldiers = ( type, soldiers ) => {
  return {
    type: types.SET_SOLDIERS,
    payload: { type, soldiers }
  }
};

export const markTask = ( type, user_ids, grid_id, date, mark ) => {
  return {
    type: types.MARK_TASK,
    payload: { type, user_ids, grid_id, date, mark }
  }
};
