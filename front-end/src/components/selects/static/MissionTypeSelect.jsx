import React, { useEffect } from 'react';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';

export const MissionTypeSelect = ({
  value, gender, onChange, ...props
}) => {

  const getOptions = () => {
    let offset = 0;

    if (gender === 'F')
      offset = 3;

    if (gender === 'M')
      offset = 2;

    return [
      { value: 0 + offset, label: 'Chabad' },
      { value: 10 + offset, label: 'Frum' },
      { value: 2 + offset, label: 'Day School' },
      { value: 4 + offset, label: 'Yeshiva School' },
      { value: 12 + offset, label: 'Friendship Circle' }
    ];
  }

  const getSelected = (options) => {
    const option = parseInt(value, 10);
    return findOption(options, option);
  }

  const options = getOptions();
  const selected = getSelected(options);

  useEffect(() => {
    // if the gender changes (re-render), we check if value needs update
    // But in functional component, we can just do this effect.
    if (gender) {
      const option = getSelected(getOptions());
      if (option && option.value !== value) {
        onChange(option);
      }
    }
  }, [gender]);

  return (
    <Select
      {...props}
      gender={gender}
      value={selected}
      onChange={onChange}
      options={options} />
  );
}

MissionTypeSelect.propTypes = {
  value: PropTypes.any,
  gender: PropTypes.oneOf(['M', 'F']),
  onChange: PropTypes.func,
}
