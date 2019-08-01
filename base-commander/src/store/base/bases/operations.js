import API from 'api/api';
import * as actions from './actions';

// get all bases
export const getBases = () => dispatch => {
  dispatch( actions.setLoading( true ) );
  return API.get( `/core/bases` )
    .then( bases => {
      dispatch( actions.setBases( bases ) );
      return dispatch( actions.setLoading( false ) );
    }).catch( () => {
      dispatch( actions.setLoading( false ) );
    });
}

// get a single base
export const getBase = id => dispatch => {
  return API.get( `/core/bases?id=${id}` )
}

// get a single base
export const getDefaults = () => dispatch => {
  return API.get( `/core/bases?action=defaults` )
}

// update a single base
export const updateBase = ( id, data ) => dispatch => {
  return API.post( `/core/bases?id=${id}`, data )
  .then( base => { 
    dispatch( actions.updateBase( id, base ) ); 
    return base;
  });
}

// register a base
export const registerBase = data => dispatch => {
  return API.post(`/core/bases?action=register`, data )
}

//********************** DOES NOT CONNECT TO REDUX **********************/

export const addPayment = ( school_id, cc ) => {
  return API.post( `/core/bases?action=addPayment`, { school_id, cc } )
}

export const deletePayment = ( school_id, payment_profile_id ) => {
  return API.post( `/core/bases?action=deletePayment`, { school_id, payment_profile_id } )
}