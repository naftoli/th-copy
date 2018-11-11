import React from 'react';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';

export const GradeSelect = ({ value, values, ...props}) => {

  const options = [
    { label: 'Pre1a', value: 'Pre1a' },
    { label: '1st Grade', value: '1' },
    { label: '2nd Grade', value: '2' },
    { label: '3nd Grade', value: '3' },
    { label: '4th Grade', value: '4' },
    { label: '5th Grade', value: '5' },
    { label: '6th Grade', value: '6' },
    { label: '7th Grade', value: '7' },
    { label: '8th Grade', value: '8' }
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
