import React, { useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getInstitutions } from 'store/base/institutions/operations';

const InstitutionSelect = ({
  institutions, getInstitutions: getInstitutionsProp, onChange, value, loading = false, ...props
}) => {

  useEffect(() => {
    if (institutions.length === 0)
      showError(getInstitutionsProp());
  }, []);

  const getOptions = () => {
    return institutions.map(
      ({ inst_name, inst_id }) => ({ value: inst_id, label: inst_name })
    );
  }

  let options = getOptions();
  const selected = findOption(options, value);
  options = loading ? [] : options;

  return (
    <Select
      {...props}
      value={selected}
      options={options}
      onChange={onChange}
      isLoading={loading} />
  )
}

InstitutionSelect.propTypes = {
  value: PropTypes.any,
  loading: PropTypes.bool,
  onChange: PropTypes.func.isRequired,
  institutions: PropTypes.array.isRequired,
  getInstitutions: PropTypes.func.isRequired
}

const mapStateToProps = ({ base }) => ({
  ...base.institutions,
})

export default connect(mapStateToProps, { getInstitutions })(InstitutionSelect);
