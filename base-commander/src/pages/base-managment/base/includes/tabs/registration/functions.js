/**
 * availableModules
 * 
 * get all available modules, as array or object.
 * 
 * @param {object} registration existing registration to hide modules
 * @param {boolean} asObject optional, return as object rather then array
 */
export const availableModules = ( registration, asObject = false ) => {
  // default modules
  let modules = [ 'chayolei', 'chidon', 'tanya', 'rewards' ];
  // if we do, then filter to what is not registered yet
  if ( registration && registration.modules ) {
    modules = modules.filter( key => !registration.modules[ key ] );
  }
  
  // convert to object with the keys as true
  if ( asObject ) {
    return modules.reduce(
      ( obj, key ) => { return { ...obj, [key]: true }; },
      {}
    );
  }
    
  // or just return the array
  return modules;
}

/**
 * getTotal
 * 
 * get the total that the base ows
 * 
 * @param {object} base the base that we are currently using
 * @param {boolean} addBalance optional, add the balance that the base owes
 */
export const getTotal = ( base, addBalance = false ) => {
  // get the modules
  let modules = availableModules( base.registration );

  let total = modules.reduce( ( total, key ) => {
    if ( base[key] && base[`${key}_fee`] )
      total += base[`${key}_fee`];
    return total;
  }, 0);

  if ( addBalance && base.balance ) {
    total += base.balance;
  }

  return total;
}

/**
 * getCart
 * 
 * get the cart to show the user
 * 
 * @param {object} base the base we need the cart for
 */
export const getCart = ( base ) => {
  // get the modules
  let modules = availableModules( base.registration );

  return modules.reduce( ( cart, key ) => {
    // if the module is selected, then add it to the cart
    if ( base[key] ) {
      return [ ...cart,{ name: key, price: base[`${key}_fee`] } ]
    }
    // return the cart
    return cart;
  }, []);
}

/**
 * moduleSelected
 * 
 * check if at least one module was selected on this base
 * 
 * @param {object} base the base to check if a module was selected for
 */
export const moduleSelected = base => {
  // get the modules
  let modules = availableModules( base.registration );

  // return true if one available module was checked
  return modules.find( key => !!base[key] ) || false;
}
