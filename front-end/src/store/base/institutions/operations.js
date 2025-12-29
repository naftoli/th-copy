import API from 'api/api';
import * as actions from './actions';

// get all bases
export const getInstitutions = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/core/institutions` )
    .then( insts => {
      dispatch( actions.setInstitutions( insts ) );
      return dispatch( actions.setLoading( false ) );
    }).catch( e => {
      dispatch( actions.setLoading( false ) );
      return Promise.reject( e );
    });
}
