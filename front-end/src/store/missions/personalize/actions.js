export const types = {
  SET_CAMPAIGNS: 'missions/personalize/set_campaigns',
  SET_TASKS: 'missions/personalize/set_tasks',
  SET_MISSIONS: 'missions/personalize/set_missions',
  PERSONALIZE: 'missions/personalize/personalize'
}

/**
 * setCampaigns
 * 
 * Dispatch standard action to reducer to set the state to these campaigns
 * 
 * @param {array} campaigns 
 */
export const setCampaigns = campaigns => {
  return {
    type: types.SET_CAMPAIGNS,
    payload: campaigns
  }
}

/**
 * setTasks
 * 
 * Dispatch standard action to reducer to update tasks
 * 
 * @param {number} subject_id the subject_id of the campaign to update
 * @param {array} tasks the tasks to set on that subject
 */
export const setTasks = ( subject_id, tasks ) => {
  return {
    type: types.SET_TASKS,
    payload: { subject_id, tasks }
  }
}

/**
 * setMissions
 * 
 * Dispatch standard action to reducer to update a tasks missions
 * 
 * @param {number} subject_id the subject_id of the tasks campaign
 * @param {string} task the name of the task these missions are for
 * @param {array} missions the array of missions to set
 */
export const setMissions = ( subject_id, task, missions ) => {
  return {
    type: types.SET_MISSIONS,
    payload: { subject_id, task, missions }
  }
}

export const personalize = updates => {
  return {
    type: types.PERSONALIZE,
    payload: updates
  }
}
