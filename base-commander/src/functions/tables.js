import React from 'react';
import moment from 'moment';
import memoizeOne from 'memoize-one';

// filter for case insensitivity and for any location in the string
export const filter = ( filter, row ) => {
  const id = filter.pivotId || filter.id;
  return row[id] !== undefined ? String(row[id]).toLowerCase().includes(filter.value.toLowerCase()) : true
}

// parse the date filter and format as substring of iso date for filtering
// this is memoized because otherwize it would be called for every row
const parseDateInput = memoizeOne(date => {
  // try parsing as day/month/year
  let parsedDate = moment(date, ["M/D/YYYY","M/D/YY"], true).format("YYYY-MM-DD")

  if (!parsedDate || parsedDate === "Invalid date") {
    // try parsing as month/day
    parsedDate = moment(date, ["M/D", "M/D/"], true).format("-MM-DD")
  }
  if (!parsedDate || parsedDate === "Invalid date") {
    // try parsing as day/ or month/
    parsedDate = moment(date, "D/", true).format("-DD")
  }
  if (!parsedDate || parsedDate === "Invalid date") {
    // try parsing as full year
    parsedDate = moment(date, "YYYY", true).format("YYYY-")
  }
  if (!parsedDate || parsedDate === "Invalid date") {
    // parse plain number by striping out non digits
    parsedDate = date.replace(/\D/g,'')
  }
  return parsedDate
})

// filter date column using US date format input
export const dateFilter = ( filter, row ) => {
  const id = filter.pivotId || filter.id;
  return row[id] !== undefined ? String(row[id]).toLowerCase().includes(parseDateInput(filter.value)) : true
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
