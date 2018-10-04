import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Select } from './Select';
// functions
import { findOption } from 'functions/selects';
import { makeCancelable } from 'functions/utils/promises';
import { getPlatoonList } from 'store/base/platoons/operations';

export class PlatoonSelect extends Component {

  static propTypes = {
    showAllOption: PropTypes.bool,
    showNoneOption: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.any,
    schoolId: PropTypes.any
  }

  static defaultProps = {
    showAllOption: false, 
    showNoneOption: false
  }

  apiRequest = null;

  state = { 
    platoons: [],
    loading: false
  }

  componentDidMount(){ 
    this.loadPlatoons(); 
  }

  // update if the login changed or the schoolId prop changed
  componentDidUpdate( { schoolId: prevId } ) {
    // if the school ID changed, get the new platoons into redux
    const { schoolId, value, isClearable, isMulti } = this.props;
    if ( prevId !== schoolId ) {
      this.loadPlatoons();
    }

    // if we have a value and it is not selected, select it
    const options = this.getOptions();
    const selected = findOption( options, value );
    if ( !selected && options.length > 0 ) {
      // if it is clearable and we have a value, clear it.
      if ( isClearable && value ) this.onChange( false );
      // if it is not clearable select the first value
      else if ( !isClearable && !isMulti ) this.onChange( options[0] );
    }
  }

  componentWillUnmount(){
    this.apiRequest && this.apiRequest.cancel();
  }

  loadPlatoons = () => {
    this.setState({ loading: true })
    return this.getPlatoons()
    .then( platoons => this.setState({ platoons, loading: false }) )
  }

  // load the platoons
  getPlatoons = () => { 
    const { schoolId } = this.props;
    if ( schoolId ) {
      this.apiRequest = makeCancelable( getPlatoonList( schoolId ) );
    } else {
      this.apiRequest = makeCancelable( 
        new Promise( resolve => resolve([]) )
      ); // resolve a promise with a new array
    }
    return this.apiRequest.promise; 
  }

  getOptions = () => {
    const { showAllOption, showNoneOption, schoolId } = this.props;
    
    const options = this.state.platoons
    // only platoons in the schoolId from props
    .filter( platoon => platoon.school_id === schoolId )
    // map them to what react-select expects
    .map( ({ class_id, name }) => ({ value: class_id, label: name }) );
    // add special options
    if ( showAllOption ) 
      options.unshift({ value: false, label: 'All Platoons' });
    else if ( showNoneOption ) 
      options.unshift({ value: false, label: 'No Platoon' });
    // and return the options
    return options;
  }

  onChange = ( option ) => {
    return this.props.onChange && this.props.onChange( option );
  }

  filter = ( option, value ) => option.label.toLowerCase().includes( value.toLowerCase() );
  
  render() {
    const { value, values } = this.props;
    const { loading } = this.state;

    let options = this.getOptions();
    let selected;
    // support single value
    if ( value )
      selected = findOption( options, value ) || null;
    // support multiple values
    if ( values )
      selected = values
        .map( value => findOption( options, value ) || false )
        .filter( value => value !== false );
    
    options = loading ? [] : options;

    return (
      <Select
        {...this.props}
        value={ selected }
        options={ options }
        isLoading={ loading }
        filterOption={ this.filter } />
    );
  }
}

export default PlatoonSelect;
