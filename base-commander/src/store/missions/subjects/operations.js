import API from 'api/api';
import * as actions from './actions';

// get all tasks
export const getSubjects = () => dispatch => {
  dispatch( actions.setLoading( 'subjects', true ) );

  return API.get( `/missions/subjects` )
    .then( subjects => {
      dispatch( actions.setSubjects( subjects ) );
      return subjects
    }).catch( e => {
      dispatch( actions.setLoading( 'subjects', false ) );
      return Promise.reject( e );
    });
}

export const getLabels = () => dispatch => {
  dispatch( actions.setLoading( 'labels', true ) );

  return API.get( `/missions/labels` )
    .then( labels => {
      dispatch( actions.setLabels( labels ) );
      return labels
    }).catch( e => {
      dispatch( actions.setLoading( 'labels', false ) );
      return Promise.reject( e );
    });
}
