import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getSubjects } from 'store/rewards/subjects/operations';

const RewardSubjectSelect = ({
  subjects, filter: filterProp, value, showTasks = false, loading, getSubjects: getSubjectsProp, ...props
}) => {

  useEffect(() => {
    showError(getSubjectsProp())
  }, []);

  const getLabel = ({ subject_name, achievement_tasks }) => {
    if (showTasks) {
      return (
        <span>
          <span className='hebrew'>{subject_name}</span> ({achievement_tasks.length} tasks)
        </span>
      );
    }
    return subject_name;
  }

  const getOptions = () => {
    let currentSubjects = subjects;

    if (filterProp)
      currentSubjects = currentSubjects.filter(filterProp);

    return currentSubjects.map(subject => ({
      value: subject.subject_id,
      label: getLabel(subject)
    }));
  }

  let options = getOptions();
  let selected = findOption(options, value) || null;

  return (
    <Select
      {...props}
      value={selected}
      options={options}
      isLoading={loading} />
  );
}

RewardSubjectSelect.propTypes = {
  onChange: PropTypes.func,
  filter: PropTypes.func,
  value: PropTypes.any,
  showTasks: PropTypes.bool
}

const mapStateToProps = ({ rewards }) => {
  return { ...rewards.subjects };
}

export default connect(mapStateToProps, { getSubjects })(RewardSubjectSelect);
