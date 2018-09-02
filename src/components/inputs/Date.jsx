import React from 'react';
import moment from 'moment';
import DatePicker from 'react-datepicker';

export const Date = ({ value, ...props }) => {
  // make sure it is a moment instance
  if ( !moment.isMoment( value ))
    value = moment( value );

  return (
    <DatePicker 
      className='form-control' readOnly
      // display formats
      dateFormat='l' 
      // dropdowns
      showMonthDropdown showYearDropdown dropdownMode='select'
      // current date and handle change
      selected={ value } onChange={ this.onDateChage }
      // override all props
      { ...props }
    />
  );
}
