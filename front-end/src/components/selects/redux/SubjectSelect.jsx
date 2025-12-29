import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getSubjects } from 'store/missions/subjects/operations';

const SubjectSelect = ({
  subjects, filter: filterProp, value, loading, getSubjects: getSubjectsProp, ...props
}) => {

  useEffect(() => {
    showError(getSubjectsProp());
  }, []);

  const getOptions = () => {
    let currentSubjects = subjects;

    if (filterProp)
      currentSubjects = currentSubjects.filter(filterProp);

    return currentSubjects.map(subject => ({
      value: subject.subject_id,
      label: subject.subject_name
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

SubjectSelect.propTypes = {
  onChange: PropTypes.func,
  filter: PropTypes.func,
  value: PropTypes.any,
}

const mapStateToProps = ({ missions }) => {
  const { subjects, loading } = missions.subjects;
  return {
    subjects,
    loading: !!loading.subjects
  };
}

export default connect(mapStateToProps, { getSubjects })(SubjectSelect);
