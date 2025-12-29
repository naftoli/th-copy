import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getMonths } from 'store/missions/shabbos_mevorchim/operations';
import { julianToMoment, julianToday } from 'functions/dates';

const SMSelect = ({
  value, onChange, isClearable, isMulti, months, loading, getMonths: getMonthsProp, ...props
}) => {

  useEffect(() => {
    getMonthsProp()
      .catch(e => toast.error(e.message));
  }, []);

  const getOptions = () => {
    // map them to what react-select expects
    return months.map(({ month, date }) => ({
      value: date,
      label: `${month} - ${julianToMoment(date).format('l')}`
    }));
  }

  const options = getOptions();

  useEffect(() => {
    // if we have a value and it is not selected, select it
    const selected = findOption(options, value);
    if (!selected && options.length > 0 && value === false) {
      // if it is clearable and we have a value, clear it.
      if (isClearable && value) onChange(false);
      // if it is not clearable select the most recent value
      else if (!isClearable && !isMulti) {
        const today = julianToday();
        onChange(options.filter(opt => opt.value <= today).pop());
      }
    }
  }, [value, options.length, isClearable, isMulti]);

  const filter = (option, val) => option.label.toLowerCase().includes(val.toLowerCase());

  let selected;
  // support single value
  if (value)
    selected = findOption(options, value) || null;
  else if (value === false)
    selected = false;

  return (
    <Select
      {...props}
      value={selected}
      options={options}
      isLoading={loading}
      isClearable={isClearable}
      isMulti={isMulti}
      onChange={onChange}
      filterOption={filter} />
  );
}

SMSelect.propTypes = {
  value: PropTypes.any,
  onChange: PropTypes.func,
}

const mapStateToProps = ({ missions }) => ({
  ...missions.shabbos_mevorchim,
});

export default connect(mapStateToProps, { getMonths })(SMSelect);
