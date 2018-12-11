import React from 'react';
import moment from 'moment';

// Format number as per the locale
export const NumberDisplay = ({ value, className, opts }) => {
  // if it is not a number, try to parse it or just return 0
  if ( typeof value !== 'number' ) {
    value = parseFloat( value, 10 ) || 0;
  }
  // convert to the local data
  value = value.toLocaleString( navigator.language, opts );
  return <span className={ className }>{value}</span>
}

// Format the number to USD currency
export const CurrencyDisplay = props => {
  // options to show US currency (what we use),
  // for french or israel change this to change currency display throught the website.
  const opts = { currency: 'USD', style: 'currency' }
  // return the number display with the currency settings
  return <NumberDisplay { ...props } opts={{ ...opts, ...props.opts }} />
}

export const DateDisplay = ({ value, calendar = false, fromNow = false, format = 'l' }) => {
  // format correctly
  if ( value ) {
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
