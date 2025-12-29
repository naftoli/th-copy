import API from 'api/api';
// import { createNotifcation, updateNotifcation } from 'functions/notifications';
import * as actions from './actions';

// load a module
export const getModule = (module) => (dispatch, getState) => {
  const modules = getState().base.modules
  // if this is in response to an update don't clear the data on what exactly was updating
  if (!modules[module] || !modules[module].loading) {
    dispatch( actions.setModuleLoading(module, true) );
  }
  return API.get( `/core/modules?id=${module}`)
    .then( data => {
      dispatch( actions.setModule( module, data ) );
      return data;
    })
    .catch( e => {
      dispatch( actions.setModuleLoading(module, false) );
      return Promise.reject( e );
    });
}

// update a module setting
export const updateModule = ( module, data ) => dispatch => {
  dispatch( actions.setModuleLoading(module, {value: data.value}) );
  return API.post( `/core/modules?id=${module}`, data )
    .then( responseData => {
      dispatch( getModule( module ) );  
      return responseData;
    })
    .catch( e => {
      dispatch( actions.setModuleLoading(module, false) );
      return Promise.reject( e );
    });
}
