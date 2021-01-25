import React from 'react';
// components
import { Select } from './Select';
// functions
import { findOption } from 'functions/selects';

const options = [
  { value: 'chabad', label: 'Chabad' },
  { value: 'frum', label: 'Frum' },
  { value: 'day_school', label: 'Day School' },
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
