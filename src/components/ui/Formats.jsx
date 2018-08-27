import React from 'react';
import moment from 'moment';

// Format number as per the locale
export const Number = ({ value }) => {
  if ( typeof value === 'number' ) 
    value = value.toLocaleString( navigator.language );
  return <span>{value}</span>
}

export const Date = ({ value, format = 'l' }) => {
  return <span>{ value ? moment( value ).format( format ) : '' }</span>
}
