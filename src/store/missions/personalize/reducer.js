import { types } from './actions';

export const initialState = [];

export default ( state = initialState, action ) => {

  const { type, payload } = action;

  // * Set the campaigns
  if ( type === types.SET_CAMPAIGNS )
    return Array.isArray( payload ) ? payload : [];

  // * Set a campaigns tasks
  if ( type === types.SET_TASKS )
    return state.map( campaign => {
      // if the subject ID's match, return a new object with the new tasks
      if ( campaign.subject_id === action.payload.subject_id )
        return { ...campaign, tasks: action.payload.tasks };
      // otherwise return the campaign untouched
      return campaign;
    });

  // * Set a tasks missions
  if ( type === types.SET_MISSIONS )
    return state.map( campaign => {
      // if the subject_id matches the correct subject
      if ( campaign.subject_id === action.payload.subject_id )
        // look at the task to see if it needs to be updated
        return { ...campaign, tasks: campaign.tasks.map( task => {
          // and the task name matches the correct task
          if ( task.task === action.payload.task )
            // update the missions on the task
            return { ...task, missions: action.payload.missions };
          // no match, return task
          return task;
        }) };
      // no match, return campaign
      return campaign;
    });

  // * update the enrolled flag based on updates
  if ( type === types.PERSONALIZE )
    return state.map( campaign => {
      // if the subject_id does not match up, do not touch this campaign
      if ( campaign.subject_id !== payload.subject_id )
        return campaign;

      // if we are editing the campaign, edit the campaign and return the result
      if ( payload.level === 'campaign' )
        return { ...campaign, enrolled: payload.enrolled };
      
      // we are updating the tasks at this point ( or their misions )
      // so we do the same thing one more level down.
      return {
        ...campaign, // keep the campaign data, 
        tasks: campaign.tasks.map( task => { // but update the tasks below
          // ignore tasks we are not updating
          if ( task.task !== payload.task )
            return task;
          
          // if we are editing the task level, update enrollment
          if ( payload.level === 'task' )
            return { ...task, enrolled: payload.enrolled };

          // a mission was checked off, so lets go one more level down
          return {
            ...task, // keep the existing task data
            missions: task.missions.map( mission => {

              // if the mission matches the payload, return the mission
              if ( mission.name !== payload.mission )
                return mission;

              // update the mission (since it is the one we are editing)
              return { ...mission, enrolled: payload.enrolled }
              
            }) // end missions map
          } // end task return
        }) // end tasks map
      } // end campaign return
    }); // end campaigns map

  // * Action does not apply to us
  return state; 
}
