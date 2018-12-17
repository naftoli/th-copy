import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { getBases } from 'store/base/bases/operations';

class BaseSelect extends Component {

  static propTypes = {
    showAllOption: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.any
  }

  static defaultProps = {
    showAllOption: false
  }

  componentDidMount(){
    if ( !this.props.bases.length )
      this.props.getBases();
  }

  componentDidUpdate() {
    const { onChange, value, isClearable } = this.props;
    // actually select the first option if is not clearable
    const options = this.getOptions();
    const selected = findOption( options, value && value.toString() );
    // if not selected and there is more then one option and we cannot clear the dropdown
    if ( !selected && options.length > 0 && !isClearable ) {
      onChange( options[0] ); 
    }
  }

  getOptions = () => {
    const { showAllOption, bases } = this.props;
    const options = bases.map( 
      ({ school_name, school_id }) => ({ value: school_id, label: school_name })
    );
    if ( showAllOption ) options.unshift({ value: false, label: 'All Bases' });
    return options;
  }

  // render
  render() {
    const { onChange, value, loading } = this.props;
    // get the dropdown options
    let options = this.getOptions();
    // get the selected option
    const selected = findOption( options, value && value.toString() );
    // if loading do not show the options
    options = loading ? [] : options;
    // rethrn the select dropdown
    return (
      <Select
        { ...this.props }
        value={ selected }
        options={ options }
        onChange={ onChange }
        isLoading={ loading } />
    );
  }
}

const mapStateToProps = ( { base } ) => ({
  ...base.bases,
})

export default connect( mapStateToProps, { getBases } )( BaseSelect );
