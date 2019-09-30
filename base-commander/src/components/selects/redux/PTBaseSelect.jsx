import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';
import { getBases } from 'store/base/bases/operations';
import API from 'api/api';

class PTBaseSelect extends Component {

  state = {
    baseAdded: false,
    bases: []
  }

  static propTypes = {
    showAllOption: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.any
  }

  static defaultProps = {
    showAllOption: false
  }

  componentDidMount(){
    if ( !this.props.bases.length ) {
      this.props.getBases();
    }
  }

  componentDidUpdate() {
    // add unassigned school only for this component once the bases from redux are available
    if ( !this.state.baseAdded && this.props.bases.length ) {
      const baseList = this.props.bases.filter( base => base.school_id !== 612 );
      API.get( `/core/bases?id=612` )
      .then( base => {
        const bases = baseList.concat( base );
        this.setState({ bases: bases, baseAdded: true });
      });
    }

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
    const { showAllOption } = this.props;
    const bases = this.state.bases;
    const options = bases.map( 
      ({ school_name, school_id }) => ({ value: school_id.toString(), label: school_name })
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

export default connect( mapStateToProps, { getBases } )( PTBaseSelect );
