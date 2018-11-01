import API from 'api/api';
import * as actions from './actions';

/**
 * getCampaigns
 * 
 * loads the campaigns with the provided filters into the state
 * 
 * @param {school_id, class_id, soldier_id} data 
 */
export const getCampaigns = data => dispatch => {
  return API.post( '/missions/personalize?action=getCampaigns', data )
    .then( campaigns => {
      dispatch( actions.setCampaigns( campaigns ) );
      return campaigns;
    });
}

/**
 * getTasks
 * 
 * load the tasks for a single campaign
 * 
 * @param {number} subject_id subject/campaign id to get tasks for
 * @param {object} data the filters to send to the API (e.g. school_id, class_id, user_id, parsha_id )
 */
export const getTasks = ( subject_id, data ) => dispatch => {
  data = { ...data, subject_id };

  return API.post( '/missions/personalize?action=getTasks', data )
    .then( tasks => {
      dispatch( actions.setTasks( subject_id, tasks ) );
      return tasks;
    });
}

/**
 * getMissions
 * 
 * load the missions for a single task
 * 
 * @param {number} subject_id subject_id/campaign id for the missions task
 * @param {string} task name of the task we are getting the missions for 
 * @param {object} data filters to sent to the api
 */
export const getMissions = ( subject_id, task, data ) => dispatch => {
  data = { ...data, subject_id, task };

  return API.post( '/missions/personalize?action=getMissions', data )
    .then( missions => {
      dispatch( actions.setMissions( subject_id, task, missions ) );
      return missions;
    });
}

/**
 * personalize
 * 
 * update a campaign/task/mission
 * 
 * @param {object} updates the updates to sent to the API
 * @param {object} data filters to be sent to the api
 */
export const personalize = ( updates, data ) => dispatch => {
  data = { ...data, updates };
  // update the UI
  dispatch( actions.personalize( updates ) );
  // call the API
  return API.post( '/missions/personalize', data );
}
