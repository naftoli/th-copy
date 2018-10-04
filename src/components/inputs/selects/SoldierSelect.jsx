import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Select } from './Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
import { makeCancelable } from 'functions/utils/promises';
import { getSoldierList } from 'store/base/soldiers/operations';

export class SoldierSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    value: PropTypes.any,
    classId: PropTypes.any,
  }

  static defaultProps = {
    showAllOption: false
  }

  apiRequest = null;

  state = { 
    soldiers: [],
    loading: false
  }

  componentDidMount(){ 
    this.loadSoldiers(); 
  }

  // update if the login changed or the schoolId prop changed
  componentDidUpdate( { classId: prevId } ) {
    // if the school ID changed, get the new soldiers into redux
    const { classId } = this.props;
    if ( prevId !== classId ) {
      this.loadSoldiers();
    }
  }

  componentWillUnmount(){
    this.apiRequest && this.apiRequest.cancel();
  }

  loadSoldiers = () => {
    this.setState({ loading: true })
    return this.getSoldiers()
    .then( soldiers => this.setState({ soldiers, loading: false }) )
    .catch( e => toast.error( e.message ) );
  }

  // load the soldiers
  getSoldiers = () => { 
    const { classId } = this.props;
    if ( classId ) {
      this.apiRequest = makeCancelable( getSoldierList( classId ) );
    } else {
      this.apiRequest = makeCancelable( 
        new Promise( resolve => resolve([]) )
      ); // resolve a promise with a new array
    }
    return this.apiRequest.promise; 
  }

  getOptions = () => {
    const { showAllOption, classId } = this.props;
    
    const options = this.state.soldiers
    // only soldiers in the classId from props
    .filter( soldier => soldier.class_id === classId )
    // map them to what react-select expects
    .map( ({ user_id, name }) => ({ value: user_id, label: name }) );
    // add special options
    if ( showAllOption ) 
      options.unshift({ value: false, label: 'All Soldiers' });
    // and return the options
    return options;
  }

  onChange = ( option ) => {
    return this.props.onChange && this.props.onChange( option );
  }

  filter = ( option, value ) => option.label.toLowerCase().includes( value.toLowerCase() );
  
  render() {
    const { value, values, showAllOption } = this.props;
    const { loading } = this.state;

    let options = this.getOptions();
    let selected;
    // support single value
    if ( value )
      selected = findOption( options, value ) || null;
    else if ( value === false && showAllOption && !loading )
      selected = options[0];
    else if ( value === false )
      selected = false;
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

export default SoldierSelect;
