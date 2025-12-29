import React, { useState, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';
// components
import { Select } from './static/Select';
// functions
import { findOption } from 'functions/selects';
import { makeCancelable } from 'functions/utils/promises';
import { getPlatoonList } from 'store/base/platoons/operations';

export const PlatoonSelect = ({
  showAllOption = false, showNoneOption = false,
  onChange, value, values, schoolId, isClearable, isMulti, ...props
}) => {

  const [platoons, setPlatoons] = useState([]);
  const [loading, setLoading] = useState(false);
  const apiRequest = useRef(null);

  useEffect(() => {
    loadPlatoons();

    return () => {
      if (apiRequest.current) {
        apiRequest.current.cancel();
      }
    }
  }, [schoolId]); // Reload if schoolId changes

  const loadPlatoons = () => {
    setLoading(true);
    getPlatoons()
      .then(p => {
        setPlatoons(p);
        setLoading(false);
      })
      .catch(e => {
        if (e && e.isCanceled) return;
        console.error(e);
        setLoading(false);
      });
  }

  const getPlatoons = () => {
    if (schoolId) {
      apiRequest.current = makeCancelable(getPlatoonList(schoolId));
    } else {
      apiRequest.current = makeCancelable(
        new Promise(resolve => resolve([]))
      );
    }
    return apiRequest.current.promise;
  }

  const getOptions = () => {
    let currentSchoolId = schoolId;
    if (typeof currentSchoolId === 'number') {
      currentSchoolId = currentSchoolId.toString();
    }
    // filter the platoons with the following criteria
    const options = platoons
      .filter(platoon => platoon.school_id === currentSchoolId)
      .map(({ class_id, name }) => ({ value: class_id, label: name }));

    if (showAllOption) {
      options.unshift({ value: false, label: 'All Platoons' });
    } else if (showNoneOption) {
      options.unshift({ value: false, label: 'No Platoon' });
    }
    return options;
  }

  const options = getOptions();

  // useEffect to handle default selection logic
  useEffect(() => {
    const selected = findOption(options, value && value.toString());

    if (!selected && options.length > 0) {
      if (isClearable && value) onChange(false);
      else if (!isClearable && !isMulti) onChange(options[0]);
    }
  }, [value, isClearable, isMulti, options.length]);
  // Note: options is a new array every render, so careful with dependency.
  // Actually getOptions depends on platoons and props.
  // If we put options.length and options[0] in dependency it might be better but options is complex.
  // We can rely on platoons changing or value changing.
  // If platoons change, options change.
  // If schoolId changes, platoons change eventually.

  const filter = (option, val) => option.label.toLowerCase().includes(val.toLowerCase());

  let selected;
  if (value)
    selected = findOption(options, value.toString()) || null;
  if (values)
    selected = values
      .map(val => findOption(options, val && val.toString()) || false)
      .filter(val => val !== false);

  const displayOptions = loading ? [] : options;

  return (
    <Select
      {...props}
      value={selected}
      options={displayOptions}
      isLoading={loading}
      filterOption={filter}
      isClearable={isClearable}
      isMulti={isMulti}
      onChange={onChange} />
  );
}

PlatoonSelect.propTypes = {
  showAllOption: PropTypes.bool,
  showNoneOption: PropTypes.bool,
  onChange: PropTypes.func,
  value: PropTypes.any,
  schoolId: PropTypes.any
}

export default PlatoonSelect;
