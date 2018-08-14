import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import Select from './Select';
// functions
import { loginStoreChanged } from 'functions/login';
import { findOption } from 'functions/selects';
import { makeCancelable } from 'functions/utils/promises';
import { getBaseList } from 'store/bases/operations';

class BaseSelect extends Component {

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

  state = { 
    bases: [],
    loading: false
  }

  apiRequest = null;

  componentDidMount(){
    this.getBases( this.props.fetchAll );
  }

  // update if the login changed and we did not fetch all bases for this BC
  componentDidUpdate( prevProps ) {
    const { fetchAll, onChange, value, isClearable } = this.props;
    // fetch new bases if we need to
    if ( loginStoreChanged( prevProps.login ) ) {
      this.getBases( fetchAll );
    }
    // actually select the first option if is not clearable
    const options = this.getOptions();
    const selected = findOption( options, value );
    if ( !selected && options.length > 0 && !isClearable ) { 
      onChange( options[0] ); 
    }
  }

  componentWillUnmount(){
    this.apiRequest && this.apiRequest.cancel();
  }

  getBases = () =>  {
    this.setState({ loading: true });
    this.apiRequest = makeCancelable( getBaseList() );
    return this.apiRequest.promise
    .then( bases => this.setState({ bases, loading: false }) )
  }

  getOptions = () => {
    const { showAllOption } = this.props;
    const options = this.state.bases.map( 
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
  login: login.current_login
})

export default connect( mapStateToProps )( BaseSelect );
