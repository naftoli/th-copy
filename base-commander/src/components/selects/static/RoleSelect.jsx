import React from 'react';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';

export const RoleSelect = ({ value, values, ...props}) => {

  const options = [
    { value: 'staff',   label: 'Staff Member' },
    { value: 'class',   label: 'Teacher' },
    { value: 'school',  label: 'Base Commander' }
  ];

  let selected;
  // support single value
  if ( value )
    selected = findOption( options, value ) || null;
  // support multiple values
  if ( values && values.length > 0 )
    selected = values
      .map( value => findOption( options, value ) || false )
      .filter( value => value !== false );

  return (
    <Select
      { ...props }
      value={ selected }
      options={ options } />
  );
}
