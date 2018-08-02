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
