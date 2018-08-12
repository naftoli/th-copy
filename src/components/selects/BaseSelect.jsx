import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import Select from './Select';
// functions
import { getBases } from 'store/bases/operations';
import { loginChanged } from 'functions/login';
import { findOption } from 'functions/selects';

export class BaseSelect extends Component {

  static propTypes = {
    showAllOption: PropTypes.bool,
    fetchAll: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.any
  }

  static defaultProps = {
    showAllOption: false,
    fetchAll: false
  }


  componentDidMount(){
    this.props.getBases( this.props.fetchAll );
  }

  // update if the login changed and we did not fetch all bases for this BC
  componentDidUpdate( prevProps ) {
    const { login, fetchAll, getBases, onChange, value, isClearable } = this.props;
    // fetch new bases if we need to
    if ( loginChanged( login, prevProps.login ) ) {
      getBases( fetchAll );
    }
    // actually select the first option if is not clearable
    const options = this.getOptions();
    const selected = findOption( options, value );
    if ( !selected && options.length > 0 && !isClearable ) { 
      onChange( options[0] ); 
    }
  }

  getOptions = () => {
    const { bases, showAllOption } = this.props;
    const options = bases.map( 
      ({ school_name, school_id }) => ({ value: school_id, label: school_name })
    );
    if ( showAllOption ) options.unshift({ value: false, label: 'All Bases' });
    return options;
  }

  // render
  render() {
    const { onChange, value, loading } = this.props;
    
    let options = this.getOptions();
    const selected = findOption( options, value );
    options = loading ? [] : options;

    return (
      <Select { ...this.props } options={ options } value={ selected }
        isLoading={ loading } onChange={ onChange } />
    )
  }
}

const mapStateToProps = ( { bases, login } ) => ({
  ...bases,
  login: login.current_login
})

export default connect( mapStateToProps, { getBases } )( BaseSelect );
