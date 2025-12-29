import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import moment from 'moment';
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getParshos } from 'store/missions/parshos/operations';

const ParshaSelect = ({
  value, values, onChange,
  endDate = false, startDate = false, isDescending = false,
  parshos, loading, isClearable, isMulti, getParshos: getParshosProp,
  ...props
}) => {

  useEffect(() => {
    getParshosProp()
      .catch(e => toast.error(e.message));
  }, []);

  const julianToGregorian = julianDate => {
    const jd = Math.floor(julianDate) + 0.5;
    const z = Math.floor(jd);
    const f = jd - z;

    let a;
    if (z < 2299161) {
      a = z;
    } else {
      const alpha = Math.floor((z - 1867216.25) / 36524.25);
      a = z + 1 + alpha - Math.floor(alpha / 4);
    }

    const b = a + 1524;
    const c = Math.floor((b - 122.1) / 365.25);
    const d = Math.floor(365.25 * c);
    const e = Math.floor((b - d) / 30.6001);

    const day = b - d - Math.floor(30.6001 * e) + f;
    const month = e < 14 ? e - 1 : e - 13;
    const year = month > 2 ? c - 4716 : c - 4715;

    // Format the date as yyyy-mm-dd
    const formattedDate = `${year}-${String(month).padStart(2, '0')}-${String(Math.floor(day)).padStart(2, '0')}`;

    return formattedDate;
  }

  const getOptions = () => {
    let options = parshos;
    if (options === undefined)
      return [];

    // if an endDate is provided, find all parshos that start by that date
    if (endDate)
      options = options.filter(parsha => parsha.end < endDate);
    // if a startDate is provided, find all parshos end on that date or later.
    if (startDate)
      options = options.filter(parsha => parsha.start > startDate)

    if (isDescending) {
      // Avoid mutating props directly if it's from redux, although redux usually gives new arrays?
      // filter returns a new array. But props.parshos is directly used if no filter.
      // Better to slice() before reverse or use toReversed() if environment supports (node 20+).
      // Since environment is unknown, use slice().
      options = options.slice().reverse();
    }

    // map them to what react-select expects
    return options.map(({ id, name, start }) => ({
      value: id,
      label: `${name} - ${moment(julianToGregorian(start)).format('l')}`
    }));
  }

  const options = getOptions();

  useEffect(() => {
    const selected = findOption(options, value);
    if (!selected && options.length > 0 && value === false) {
      // if it is clearable and we have a value, clear it.
      if (isClearable && value) onChange(false);
      // if it is not clearable select the first value
      else if (!isClearable && !isMulti) onChange(options[0]);
    }
  }, [value, options.length, isClearable, isMulti]);

  const filter = (option, val) => option.label.toLowerCase().includes(val.toLowerCase());

  let selected;
  // support single value
  if (value)
    selected = findOption(options, value) || null;
  else if (value === false)
    selected = false;
  // support multiple values
  if (isMulti && values)
    selected = values
      .map(val => findOption(options, val) || false)
      .filter(val => val !== false);

  const displayOptions = loading ? [] : options;

  return (
    <Select
      {...props}
      value={selected}
      options={displayOptions}
      isLoading={loading}
      isClearable={isClearable}
      isMulti={isMulti}
      onChange={onChange}
      filterOption={filter} />
  );
}

ParshaSelect.propTypes = {
  value: PropTypes.any,
  values: PropTypes.array,
  onChange: PropTypes.func,
}

const mapStateToProps = ({ missions }) => {
  return {
    loading: missions.parshos.loading,
    parshos: missions.parshos.parshos,
  };
}

export default connect(mapStateToProps, { getParshos })(ParshaSelect);
