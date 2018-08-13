import is from 'is_js';

// filter for case insensitivity and for any location in the string
export const filter = ( filter, row ) => {
  const id = filter.pivotId || filter.id;
  return row[id] !== undefined ? String(row[id]).toLowerCase().includes(filter.value.toLowerCase()) : true
}

// scroll to the top of the table
export const scrollToTop = ( id ) => () => { 
  const table = document.querySelector(`#${id} .rt-tbody`)
  if ( table ) table.scrollTop = 0;
}

/**
 * toggleRowBasic( component )
 * @returns Promise
 * 
 * Relies on component to have a selection key in it's state.
 * 
 * @prop component a component with .setState and .state.selection
 */
export const toggleRowBasic = ( component, collection, checkAll, id ) => {
  return new Promise( resolve => {
    let selection = [ ...component.state.selection ];
    // get the index of the item if it exists
    if (selection.indexOf( id ) >= 0) {
      selection = selection.filter( item => item !== id );
    } else {
      selection.push( id );
    }
    const selectAll = collection.length === selection.length
    component.setState({ selection, selectAll });
    resolve( selection );
  })
}

export const toggleAllBasic = ( component, reactTable, getId ) => {
  return new Promise( resolve => {
    const selectAll = !component.state.selectAll;
    const selection = [];
    if ( selectAll ) {
      reactTable.getResolvedState().sortedData.forEach( 
        item => selection.push( getId( item ) ) 
      );
    }
    component.setState({ selection, selectAll });
    resolve( selection );
  });  
}

export const defaultTableProps = ( id, loading = false ) => {
  const onChange = scrollToTop( id );
  return {
    className: "-striped -highlight",
    filterable: true, defaultFilterMethod: filter,
    minRows: is.mobile() || is.tablet() ? 10 : 15,
    noDataText: loading ? 'Loading...' : 'No Data',
    onPageChange: onChange, onFilteredChange: onChange,
    defaultPageSize: is.mobile() || is.tablet() ? 50 : 100,
  }
}
