import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import _ from 'lodash';
// components
import { Select } from '../static/Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getSoldiers } from 'store/base/soldiers/operations';

export const SoldierSelect = ({
  onChange, value, values,
  classId, classIds, schoolId,
  showAllOption = false, registeredOnly = false,
  onlyReloadSoldiersIfNotLoaded = false,
  soldiers, loading, getSoldiers: getSoldiersProp, isClearable, isMulti,
  ...props
}) => {

  const loadSoldiers = () => {
    if (!onlyReloadSoldiersIfNotLoaded || !(soldiers && soldiers.length > 0)) {
      getSoldiersProp()
        .catch(e => toast.error(e.message));
    }
  }

  useEffect(() => {
    loadSoldiers();
  }, []); // DidMount

  useEffect(() => {
    loadSoldiers();
  }, [schoolId, classId, JSON.stringify(classIds)]);

  const getOptions = () => {
    // make sure the school id is valid
    let currentSchoolId = schoolId;
    if (!currentSchoolId) {
      return [];
    } else {
      currentSchoolId = currentSchoolId.toString();
    }

    let options = soldiers;

    // only soldiers in the classId from props
    let currentClassId = classId;
    if (currentClassId) {
      currentClassId = currentClassId.toString();
      options = options.filter(soldier => soldier.class_id === currentClassId);
      // allow for multi select
    } else if (classIds && classIds.length > 0) {
      const currentClassIds = classIds.map(id => id.toString());
      options = options.filter(soldier => currentClassIds.includes(soldier.class_id));
      // make sure the soldier is in a platoon
    } else {
      options = options.filter(soldier => !!soldier.class_id);
    }

    // limit to school ID
    if (currentSchoolId) {
      options = options.filter(soldier => soldier.school_id === currentSchoolId);
    }

    // limit to registered only
    if (registeredOnly) {
      options = options.filter(soldier => !!soldier.user_registered);
    }

    // map them to what react-select expects
    options = options.map(({ user_id, first, last }) => (
      { value: user_id, label: `${first} ${last}` }
    ));
    // add special options
    if (showAllOption && !isMulti) {
      options.unshift({ value: false, label: 'All Soldiers' });
    }
    // and return the options
    return options;
  }

  const options = getOptions();

  useEffect(() => {
    const selected = findOption(options, value);
    if (!selected && options.length > 0) {
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
  else if (value === false && showAllOption && !loading)
    selected = options[0];
  else if (value === false)
    selected = false;

  // support multiple values
  if (values)
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

SoldierSelect.propTypes = {
  onChange: PropTypes.func,
  value: PropTypes.any,
  classId: PropTypes.any,
  classIds: PropTypes.array,
  schoolId: PropTypes.any,
  showAllOption: PropTypes.bool,
  registeredOnly: PropTypes.bool,
  onlyReloadSoldiersIfNotLoaded: PropTypes.bool,
  soldiers: PropTypes.array,
  loading: PropTypes.bool,
  getSoldiers: PropTypes.func,
  isClearable: PropTypes.bool,
  isMulti: PropTypes.bool
}

const mapStateToProps = ({ base }) => {
  return {
    loading: base.soldiers.loading,
    soldiers: base.soldiers.soldiers,
  };
}

export default connect(mapStateToProps, { getSoldiers })(SoldierSelect);
