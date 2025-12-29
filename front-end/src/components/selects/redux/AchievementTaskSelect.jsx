import React from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';

const AchievementTaskSelect = ({
  tasks, filter: filterProp, value, showMiles = false, subjectId, loading, ...props
}) => {

  const getLabel = ({ task, points }) => {
    if (showMiles) {
      return (
        <span>
          <span className='hebrew'>{task}</span> ({points} miles)
        </span>
      );
    }
    return task;
  }

  const getOptions = () => {
    let currentTasks = tasks;

    if (filterProp) {
      currentTasks = currentTasks.filter(filterProp);
    }

    if (subjectId) {
      currentTasks = currentTasks.filter(task => task.subject_id === subjectId);
    }

    return currentTasks.map(task => ({
      value: task.achievement_task_id,
      label: getLabel(task)
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

AchievementTaskSelect.propTypes = {
  onChange: PropTypes.func,
  filter: PropTypes.func,
  value: PropTypes.any,
  showMiles: PropTypes.bool
}

const mapStateToProps = ({ rewards }) => {
  return { ...rewards.achievement_tasks };
}

export default connect(mapStateToProps)(AchievementTaskSelect);
