import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import Select from './Select';
// functions
import { getPlatoonList } from 'store/platoons/operations';
import { findOption } from 'functions/selects';

export class PlatoonSelect extends Component {

  static propTypes = {
    showAllOption: PropTypes.bool,
    showNoneOption: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.any,
    school_id: PropTypes.any.isRequired,
    // fetchAll: PropTypes.bool
  }

  static defaultProps = {
    showAllOption: false, 
    showNoneOption: false,
    // fetchAll: false
  }

  state = { 
    platoons: [],
    loading: false
  }

  componentDidMount(){ this.loadPlatoons(); }

  // update if the login changed or the school_id prop changed
  componentDidUpdate( { school_id: prevId } ) {
    // if the school ID changed, get the new platoons into redux
    const { school_id, onChange, value, isClearable } = this.props;
    if ( prevId !== school_id ) {
      this.loadPlatoons();
    }

    // if we have a value and it is not selected, select it
    const options = this.getOptions();
    const selected = findOption( options, value );
    if ( !selected && options.length > 0 ) {
      // if it is clearable and we have a value, clear it.
      if ( isClearable && value ) onChange( false );
      // if it is not clearable select the first value
      else if ( !isClearable ) onChange( options[0] );
    }
  }

  loadPlatoons = () => {
    this.setState({ loading: true })
    return this.getPlatoons()
    .then( platoons => this.setState({ platoons, loading: false }) )
  }

  // load the platoons
  getPlatoons = () => { 
    const { school_id } = this.props;
    if ( school_id ) 
      return getPlatoonList( school_id ); 
    // else if ( fetchAll )
    //   return getPlatoonList();
    else
      return new Promise( resolve => resolve([]) ); // resolve a promise with a new array
  }

  getOptions = () => {
    const { showAllOption, showNoneOption, school_id } = this.props;
    
    const options = this.state.platoons
    // only platoons in the school_id from props
    .filter( platoon => platoon.school_id === school_id )
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
  
  render() {
    const { value, onChange } = this.props;
    const { loading } = this.state;

    let options = this.getOptions();
    const selected = findOption( options, value ) || null;
    options = loading ? [] : options;

    return (
      <Select {...this.props} options={ options } value={ selected }
        isLoading={ loading } onChange={ onChange }/>
    )
  }
}

export default PlatoonSelect;
