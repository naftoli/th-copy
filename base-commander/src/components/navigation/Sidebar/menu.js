import menu from './menu.json';

// constants
const DEFAULT_USER_TYPES = [ 'HQ', 'INST', 'BC' ];

/**
 * menuReducer
 * 
 * Reducer for items in menu to limit by type (code) provided
 * 
 * @param {string} user_type The type of user (e.g. HQ, INSTITUTION, BC, PARENT) that we are filtering the menu for
 * @param {Array} defaults Array of defaults in the event that the item does not have a user_types array
 * 
 * @returns {function} Returns a valid reducer to be passed to .reduce with an array as the initilaizer
 */
export const menuReducer = ( login, defaults = DEFAULT_USER_TYPES ) => ( filtered = [], item ) => {
  const { code, legacy, modules } = login;
  // reduce the items down a bit
  if ( item.items ) {
    item = Object.assign( {}, item, 
      { items: item.items.reduce(
        menuReducer( login, item.user_types || defaults ), 
        []
      )}
    );
  }

  if (
    ( !legacy && item.legacy ) // hide legacy links from new institutions
    || ( item.module && modules && !modules[ item.module ] ) // hide menu if login does not have access to this module
  ) return filtered;
  
  // if the item is enabled for that code, add it to the sidebar
  if ( item.user_types && item.user_types.indexOf( code ) > -1 ) {
    filtered.push( item );
  // if the default for this section contains this code, add it
  } else if ( !item.user_types && defaults.indexOf( code ) > -1 ) {
    filtered.push( item );
  }
  
  return filtered;
}

/**
 * getMenu
 * 
 * returns an array for the user_type provided to get their sidebar menu
 * 
 * @param {string} user_type The type of user to get the menu for 
 */
const getMenu = ( login ) => {

  if ( Object.keys( login ).length === 0 )
    return [];

  // Define the shape of the menu

  // filter the menu and return it
  return menu.reduce( menuReducer( login ), [] );
} // end getMenu function

// export getMenu by default
export default getMenu;