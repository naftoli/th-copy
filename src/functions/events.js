import { toJulian } from "./dates";

// filter updates to only ones which are different from the source object. should be Object.filter( key, value => res ) but hey..
export const filterUpdates = ( source, updates ) => {
  return Object.entries( updates ) // convert to array ( for all the cool functions )
    // filter to entries who's value does not match that of the same key in the source
    .filter( entry => source[entry[0]] !== entry[1] )
    // and convert that back to an object using reduce
    .reduce( ( obj, item ) => ({ ...obj, [item[0]]: item[1] }), {} )
}

// * handle raw inputs
export const onInputChange = ( callback, key = 'name' ) => ({ target }) =>
  callback && callback( { [ target[key] ]: target.value } );

// * handle changes that need to be parsed with JSON
export const onJSONChange = ( callback, key = 'name' ) => ({ target }) =>
  callback && callback( { [ target[key] ]: JSON.parse( target.value ) } );

// * handle inputs that need to be converted to numbers
export const onNumberChange = ( callback, key = 'name' ) => ({ target }) =>
  callback && callback( { [target.name]: target.value ? parseFloat( target.value, 10 ) : null } );

// * convert date to Julian Date
export const onJulianDateChange = callback => key => date =>
  callback && callback({ [key]: toJulian( date ) });

// * handle react-select changes
export const onSelectChange = callback => key => option =>
  callback && callback({ [key]: option && option.value });

// * handle isMulti select dropdowns
export const onMultiSelectChange = callback => key => options =>
  callback && callback({ [key]: options ? options.map( option => option.value ) : [] });

// * handle checkbox inputs (return 1 or 0)
export const onCheckboxChange = ( callback, toNumber = true ) => ({ target }) => {
  // 1 or 0
  if ( toNumber )
    return callback && callback( { [target.name]: target.checked ? 1 : 0 } );
  // True or False
  return callback && callback( { [target.name]: target.checked } );
}
