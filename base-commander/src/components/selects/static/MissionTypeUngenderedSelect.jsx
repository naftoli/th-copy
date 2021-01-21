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

export function MissionTypeUngenderedSelect(props) {
  let selected = findOption(options, props.value) || false;

  return (
    <Select
      {...props}
      value={selected}
      options={options} />
  );
}

// MissionTypeUnGenderedSelect.propTypes = Select.propTypes
