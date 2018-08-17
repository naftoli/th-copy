import React from 'react';

// Format number as per the locale
export const Number = ({ value }) => {
  if ( typeof value === 'number' ) 
    value = value.toLocaleString( navigator.language );
  return <span>{value}</span>
}
