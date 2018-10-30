import React from 'react';
import moment from 'moment';

// Format number as per the locale
export const NumberDisplay = ({ value, className, opts }) => {
  if ( !typeof value === 'number' )
    value = parseInt( value, 10 );
  // convert to the local data
  value = value.toLocaleString( navigator.language, opts );
  return <span className={ className }>{value}</span>
}

export const DateDisplay = ({ value, calendar = false, fromNow = false, format = 'l' }) => {
  // format correctly
  if ( value ){
    value = moment( value );

    if ( calendar )
      value = value.calendar();

    else if ( fromNow )
      value = value.fromNow();

    else if ( format )
      value = value.local().format( format );
    
  }

  return <span>{ value || '' }</span>
}
