import { toHebrew } from 'functions/utils';

// handle hebrew text inputs
export const hebrewChange = onChange => event => {
  event.target.value = toHebrew( event.target.value );
  onChange( event )
}
// convert events to { target.key: target.value };
export const eventToUpdate = ( target, key = 'name' ) => {
  return { [target[key]]: target.value }
}

// filter updates to only ones which are different from the source object. should be Object.filter( key, value => res ) but hey..
export const filterUpdates = ( source, updates ) => {
  return Object.entries( updates ) // convert to array ( for all the cool functions )
    // filter to entries who's value does not match that of the same key in the source
    .filter( entry => source[entry[0]] !== entry[1] )
    // and convert that back to an object using reduce
    .reduce( ( obj, item ) => ({ ...obj, [item[0]]: item[1] }), {} )
}
