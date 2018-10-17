export const types = {
  SET_LOADING: 'missions/mark/set_loading',
  SET_MISSIONS: 'missions/mark/set_missions',
  MARK_MISSON: 'missions/mark/mark_mission',
}

export const setLoading = loading => {
  return {
    type: types.SET_LOADING,
    payload: loading
  }
};

export const setMissions = missions => {
  return {
    type: types.SET_MISSIONS,
    payload: missions
  }
};

export const markMission = ( date_task_id, dates, mark ) => {
  return {
    type: types.MARK_MISSON,
    payload: { date_task_id, dates, mark }
  }
};
