import API from 'api/api';
import * as actions from './actions';

// get all tasks
export const getSubjects = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/rewards/subjects` )
    .then( ({ data }) => {
      dispatch( actions.setSubjects( data ) );
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}
