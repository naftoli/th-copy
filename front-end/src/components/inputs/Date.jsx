import React from 'react';
import moment from 'moment';
import DatePicker from 'react-datepicker';

export const Date = ({ value, ...props }) => {
  // Convert moment object to JS Date for DatePicker v9+
  const selectedDate = value ? moment(value).toDate() : null;

  // Handle change: convert JS Date back to moment for parent component
  const handleChange = (date) => {
    if (props.onChange) {
      props.onChange(date ? moment(date) : null);
    }
  };

  return (
    <DatePicker
      className='form-control' autoComplete='off'
      // display formats
      dateFormat='P' // 'P' is date-fns localized date (was 'l' in moment)
      placeholderText={moment().format(props.dateFormat || 'l')}
      // dropdowns
      showMonthDropdown showYearDropdown dropdownMode='select'
      // spread props first so we can override onChange and selected
      {...props}
      // overrides
      selected={selectedDate}
      onChange={handleChange}
    />
  );
}
