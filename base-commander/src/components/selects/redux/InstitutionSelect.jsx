import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { showError } from 'functions/notifications';
import { getInstitutions } from 'store/base/institutions/operations';

class InstitutionSelect extends Component {

  static propTypes = {
    value: PropTypes.any,
    loading: PropTypes.bool,
    onChange: PropTypes.func.isRequired,
    institutions: PropTypes.array.isRequired,
    getInstitutions: PropTypes.func.isRequired
  }

  static defaultProps = {
    loading: false
  }

  componentDidMount(){
    if ( this.props.institutions.length === 0 )
      showError( this.props.getInstitutions() );
  }

  getOptions = () => {
    return this.props.institutions.map(
      ({ inst_name, inst_id }) => ({ value: inst_id, label: inst_name })
    );
  }

  // render
  render() {
    const { onChange, value, loading } = this.props;
    
    let options = this.getOptions();
    const selected = findOption( options, value );
    options = loading ? [] : options;

    return (
      <Select
        { ...this.props }
        value={ selected }
        options={ options }
        onChange={ onChange }
        isLoading={ loading } />
    )
  }
}

const mapStateToProps = ( { base } ) => ({
  ...base.institutions,
})

export default connect( mapStateToProps, { getInstitutions } )( InstitutionSelect );
