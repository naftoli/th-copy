import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getLabels } from 'store/missions/subjects/operations';

const LabelSelect = ({
  labels, filter: filterProp, value, loading, getLabels: getLabelsProp, ...props
}) => {

  useEffect(() => {
    showError(getLabelsProp())
  }, []);

  const getOptions = () => {
    let currentLabels = labels;

    if (filterProp)
      currentLabels = currentLabels.filter(filterProp);

    return currentLabels.map(label => ({
      value: label.label_id,
      label: `${label.label_name} - ${label.frequency.frequency_name}`
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

LabelSelect.propTypes = {
  onChange: PropTypes.func,
  filter: PropTypes.func,
  value: PropTypes.any,
}

const mapStateToProps = ({ missions }) => {
  const { labels, loading } = missions.subjects;
  return {
    labels,
    loading: !!loading.labels
  };
}

export default connect(mapStateToProps, { getLabels })(LabelSelect);
