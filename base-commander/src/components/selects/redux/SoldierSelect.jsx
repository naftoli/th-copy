import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getSoldiers } from 'store/base/soldiers/operations';

export class SoldierSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    value: PropTypes.any,

    classId: PropTypes.any,
    classIds: PropTypes.array,

    schoolId: PropTypes.any,
  }

  static defaultProps = {
    showAllOption: false,
    registeredOnly: false
  }

  componentDidMount() {
    this.loadSoldiers();
  }

  // update if the login changed or the schoolId/classId prop changed
  componentDidUpdate({ schoolId: prevSchoolId, classId: prevClassId }) {
    // if the school ID changed, get the new platoons into redux
    const { schoolId, value, isClearable, isMulti, classId } = this.props;
    if ((prevSchoolId !== schoolId) || (prevClassId !== classId)) {
      this.loadSoldiers();
    }

    // if we have a value and it is not selected, select it
    const options = this.getOptions();
    const selected = findOption(options, value);

    console.log('this is the options', options)
    console.log('this is the value', value)
    console.log('this is the selected', selected)
    if (!selected && options.length > 0) {
      // if it is clearable and we have a value, clear it.
      if (isClearable && value) this.props.onChange(false);
      // if it is not clearable select the first value
      else if (!isClearable && !isMulti) this.props.onChange(options[0]);
    }
  }

  loadSoldiers = () => {
    return this.props.getSoldiers()
      .catch(e => toast.error(e.message));
  }

  getOptions = () => {
    let {
      showAllOption, isMulti,
      schoolId, registeredOnly,
      classId, classIds, soldiers
    } = this.props;

    // make sure the school id is valid
    if (!schoolId) {
      return [];
    } else {
      schoolId = schoolId.toString();
    }

    let options = soldiers;

    // only soldiers in the classId from props
    if (classId) {
      classId = classId.toString();
      options = options.filter(soldier => soldier.class_id === classId);
      // allow for multi select
    } else if (classIds && classIds.length > 0) {
      classIds = classIds.map(id => id.toString());
      options = options.filter(soldier => classIds.includes(soldier.class_id));
      // make sure the soldier is in a platoon
    } else {
      options = options.filter(soldier => !!soldier.class_id);
    }

    // limit to school ID
    if (schoolId) {
      options = options.filter(soldier => soldier.school_id === schoolId);
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

  filter = (option, value) => option.label.toLowerCase().includes(value.toLowerCase());

  render() {
    const { value, loading, values, showAllOption } = this.props;

    let options = this.getOptions();
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
        .map(value => findOption(options, value) || false)
        .filter(value => value !== false);

    options = loading ? [] : options;

    return (
      <Select
        {...this.props}
        value={selected}
        options={options}
        isLoading={loading}
        filterOption={this.filter} />
    );
  }
}

const mapStateToProps = ({ base }) => {
  return {
    loading: base.soldiers.loading,
    soldiers: base.soldiers.soldiers,
  };
}

export default connect(mapStateToProps, { getSoldiers })(SoldierSelect);
