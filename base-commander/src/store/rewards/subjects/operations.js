import API from 'api/api';
import * as actions from './actions';

// get all tasks
export const getSubjects = () => dispatch => {
  dispatch( actions.setLoading( true ) );

  return API.get( `/rewards/subjects` )
    .then( subjects => {
      dispatch( actions.setSubjects( subjects ) );
      return subjects
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}
