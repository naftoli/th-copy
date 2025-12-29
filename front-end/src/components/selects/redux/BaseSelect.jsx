import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { getBases } from 'store/base/bases/operations';

const BaseSelect = ({
  showAllOption = false, onChange, value, bases, getBases: getBasesProp, loading, isClearable, addUnassigned, ...props
}) => {

  useEffect(() => {
    if (!bases.length)
      getBasesProp();
  }, []); // Only check on mount. If bases load dynamically, reducer update will cause re-render anyway.

  const getOptions = () => {
    const options = bases.map(
      ({ school_name, school_id }) => ({ value: school_id, label: school_name })
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

  const selected = findOption(options, value && value.toString());
  const displayOptions = loading ? [] : options;

  return (
    <Select
      {...props}
      value={selected}
      options={displayOptions}
      onChange={onChange}
      isLoading={loading}
      isClearable={isClearable} />
  );
}

BaseSelect.propTypes = {
  showAllOption: PropTypes.bool,
  onChange: PropTypes.func,
  value: PropTypes.any
}

const mapStateToProps = ({ base }) => ({
  ...base.bases,
})

export default connect(mapStateToProps, { getBases })(BaseSelect);
