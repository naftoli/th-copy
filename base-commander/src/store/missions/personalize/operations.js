import API from 'api/api';
import * as actions from './actions';
import _ from 'lodash'

/**
 * function mergeEnrollmentResponses
 * 
 * adds the ability to select multiple classes by merging the responses together
 * 
 * @param {object[][]} Responses an array of responses each containing an array of enrollmentItems
 * @param {string} uniqueKey the key used to match array items across responses
 */
 function mergeEnrollmentResponses (responses, uniqueKey) {
  return responses.reduce(
    (responseA, responseB) => {
      // get all {{uniqueKey}} names from both responses so they can be merged correctly
      // even when the classes have identicly sized and ordered responses
      const keys = _.uniq(
        responseA.map(r => r[uniqueKey]).concat(
          responseB.map(r => r[uniqueKey])
        )
      )
      const mergeResponse = keys.map(key => {
        const enrollmentA = _.find(responseA, {[uniqueKey]: key})
        const enrollmentB = _.find(responseB, {[uniqueKey]: key})
        if (!enrollmentA) { return enrollmentB }
        if (!enrollmentB) { return enrollmentA }
        const mergedEnrollment = {...enrollmentA }
        if (enrollmentA.enrolled || enrollmentB.enrolled) { mergedEnrollment.enrolled = true }
        if (enrollmentA.labels || enrollmentB.labels) {
          // todo: merge labels for tasks enrollments
        }
        return mergedEnrollment
      })
      console.log("merged responses a with b to c using key", responseA, responseB, mergeResponse, uniqueKey)
      return mergeResponse
    }
  );
}

/**
 * getCampaigns
 * 
 * loads the campaigns with the provided filters into the state
 * 
 * @param {{school_id, class_id, class_ids, soldier_id}} data 
 */
export const getCampaigns = data => dispatch => {
  if (data.class_ids && data.class_ids.length > 0) {
    return Promise.all(data.class_ids.map(class_id => {
      return API.post( '/missions/personalize?action=getCampaigns', {...data, class_id } )
    }))
      .then(responses => {
        const campaigns = mergeEnrollmentResponses(responses, 'subject_id')
        dispatch( actions.setCampaigns( campaigns ) );
        return campaigns;
      })
  }
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

  if (data.class_ids && data.class_ids.length > 0) {
    return Promise.all(data.class_ids.map(class_id => {
      return API.post( '/missions/personalize?action=getTasks', {...data, class_id } )
    }))
      .then(responses => {
        const tasks = mergeEnrollmentResponses(responses, 'task')
        dispatch( actions.setTasks( subject_id, tasks ) );
        return tasks;
      })
  }
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

  if (data.class_ids && data.class_ids.length > 0) {
    return Promise.all(data.class_ids.map(class_id => {
      return API.post( '/missions/personalize?action=getMissions', {...data, class_id } )
    }))
      .then(responses => {
        const missions = mergeEnrollmentResponses(responses, 'name')
        dispatch( actions.setMissions( subject_id, task, missions ) );
        return missions;
      })
  }
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
  if (data.class_ids && data.class_ids.length > 0) {
    return Promise.all(data.class_ids.map(class_id => {
      return API.post( '/missions/personalize', {...data, class_id } );
    }))
  }
  return API.post( '/missions/personalize', data );
}
