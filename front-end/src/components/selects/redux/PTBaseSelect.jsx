import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { getBases, getEveryBase } from 'store/base/bases/operations';
// import API from 'api/api';

const PTBaseSelect = ({
  showAllOption = false, onChange, value, bases, getBases: getBasesProp, addUnassigned, loading, isClearable, ...props
}) => {

  useEffect(() => {
    if (!bases.length) {
      getBasesProp();
    }
  }, []);

  const getOptions = () => {
    const options = bases.map(
      ({ school_name, school_id }) => ({ value: school_id.toString(), label: school_name })
    );
    if (showAllOption) options.unshift({ value: false, label: 'All Bases' });
    // for platoon transition, add the unassigned school
    if (addUnassigned && bases.length) {
      options.push({ value: "612", label: "Unassigned Students" });
    }
    return options;
  }

  const options = getOptions();

  useEffect(() => {
    const selected = findOption(options, value && value.toString());
    // if not selected and there is more then one option and we cannot clear the dropdown
    if (!selected && options.length > 0 && !isClearable) {
      onChange(options[0]);
    }
  }, [value, options.length, isClearable]);

  const selectedOption = findOption(options, value && value.toString());
  const displayOptions = loading ? [] : options;

  return (
    <Select
      {...props}
      value={selectedOption}
      options={displayOptions}
      onChange={onChange}
      isLoading={loading}
      isClearable={isClearable} />
  );
}

PTBaseSelect.propTypes = {
  showAllOption: PropTypes.bool,
  onChange: PropTypes.func,
  value: PropTypes.any
}

const mapStateToProps = ({ base }) => ({
  ...base.bases,
})

export default connect(mapStateToProps, { getBases, getEveryBase })(PTBaseSelect);
