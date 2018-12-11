import React from 'react';

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

// filter yes/no based on the truth or falseness of the column
export const yesNoFilter = ( filter, row ) => {
  if ( filter.value === 'all' ) return true;
  if ( filter.value === 'yes') return !!row[filter.id];
  if ( filter.value === 'no') return !row[filter.id];
}

// render the yes/no dropdown
export const yesNoFilterRender = ( yes = 'Yes', no = 'No' ) => ({ filter, onChange }) => (
  <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
    onChange={event => onChange(event.target.value)}>
    <option value="all">Show All</option>
    <option value="yes">{ yes }</option>
    <option value="no">{ no }</option>
  </select>
);
