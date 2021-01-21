import React from 'react';
// components
import { Select } from './Select';
// functions
import { findOption } from 'functions/selects';

const options = [
  { value: '2,3', label: 'Chabad' },
  { value: '12,13', label: 'Frum' },
  { value: '4,5', label: 'Day School' },
  // { value: '6,7', label: 'Hebrew School' },
  // { value: '14,15', label: 'Friendship Circle' }
];

/* finds the intersection of 
 * two arrays in a simple fashion.  
 *
 * PARAMS
 *  a - first array, must already be sorted
 *  b - second array, must already be sorted
 *
 * NOTES
 *
 *  Should have O(n) operations, where n is 
 *    n = MIN(a.length(), b.length())
 */
function sorted_array_int_intersects(a, b) {
  var ai = 0, bi = 0;

  while (ai < a.length && bi < b.length) {
    if (a[ai] < b[bi]) { ai++; }
    else if (a[ai] > b[bi]) { bi++; }
    else /* they're equal */ {
      return true
    }
  }

  return false;
}

export function MissionTypeUngenderedSelect(props) {
  let { value } = props;

  let selected = findOption(options, value) || false;
  if (!selected && value) {
    console.log("not selected with value: " + value);
    selected = options.find(option => sorted_array_int_intersects(
      option.value.split(",").map(v => parseInt(v, 10)),
      value.split(",").map(v => parseInt(v, 10))
    ))
  }

  console.log("selected value: " + (selected ? selected.value : false));
  console.log("props value: " + props.value);


  return (
    <Select
      {...props}
      value={selected}
      options={options} />
  );
}

// MissionTypeUnGenderedSelect.propTypes = Select.propTypes
