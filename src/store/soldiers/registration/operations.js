/**
 * This file has nothing to do with redux. 
 * It simply manages the API requests for /src/pages/users/RegistrationPage/RegistrationPage.jsx
 * It is here to keep all API requests in one place for when we plan on replacing the API layer with something half sane ;-)
 * 
 * As such they do not return another function that accepts dispatch. 
 * If you wish to add this data to the redux store for some reason. Remember to update this...
 */
import API from 'api/api';
import moment from 'moment';

// get all soldiers
export const getSoldiers = () => {
  return API.get( '/registration/users.php' )
  .then( response => {
    if ( !response.success ) 
      return Promise.reject( response );
    // format user_registered to browser locale
    var t0 = performance.now();
    const soldiers = response.data.map( soldier => Object.assign({}, soldier, { 
      user_registered: soldier.user_registered ? 
        moment( soldier.user_registered ).format('l LT') : 
        soldier.user_registered
    }));
    console.log("Formatting users response took " + (performance.now() - t0) + " milliseconds. TODO: speed up.");
    // pass to the next .then();
    return soldiers
  });
}