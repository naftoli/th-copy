// import { toHebrew } from 'functions/utils';

// convert events to { target.key: target.value };
export const eventToUpdate = ( target, key = 'name' ) => {
  return { [target[key]]: target.value }
}
// handle checkbox inputs
export const handleCheckbox = handleChange => ({ target }) => {
  handleChange( { [target.name]: target.checked ? 1 : 0 } );
}

// filter updates to only ones which are different from the source object. should be Object.filter( key, value => res ) but hey..
export const filterUpdates = ( source, updates ) => {
  return Object.entries( updates ) // convert to array ( for all the cool functions )
    // filter to entries who's value does not match that of the same key in the source
    .filter( entry => source[entry[0]] !== entry[1] )
    // and convert that back to an object using reduce
    .reduce( ( obj, item ) => ({ ...obj, [item[0]]: item[1] }), {} )
}
