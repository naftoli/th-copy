import React from 'react';
import moment from 'moment';
import DatePicker from 'react-datepicker';

export const Date = ({ value, ...props }) => {
  // make sure it is a moment instance
  if ( value && !moment.isMoment( value ))
    value = moment( value );

  return (
    <DatePicker 
      className='form-control'
      // display formats
      dateFormat='l' placeholderText={ moment().format( props.dateFormat || 'l' ) }
      // dropdowns
      showMonthDropdown showYearDropdown dropdownMode='select'
      // current date and handle change
      selected={ value } onChange={ this.onDateChage }
      // override all props
      { ...props }
    />
  );
}
