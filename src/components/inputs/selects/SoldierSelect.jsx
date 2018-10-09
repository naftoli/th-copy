import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from './Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
import { makeCancelable } from 'functions/utils/promises';
import { getSoldierList } from 'store/base/soldiers/operations';
// state
import { getSoldiers } from 'store/base/soldiers/operations';

export class SoldierSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    value: PropTypes.any,

    classId: PropTypes.any,
    classIds: PropTypes.array,

    schoolId: PropTypes.any,
    schoolIds: PropTypes.array,
  }

  static defaultProps = {
    showAllOption: false
  }

  componentDidMount(){ 
    this.loadSoldiers(); 
  }

  loadSoldiers = () => {
    return this.props.getSoldiers()
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
    const { 
      showAllOption, isMulti, 
      schoolId, schoolIds, 
      classId, classIds, soldiers 
    } = this.props;
    
    if ( !( schoolId || schoolIds ) )
      return [];

    let options = soldiers;
    // only soldiers in the classId from props
    if ( classId )
      options = options.filter( soldier => soldier.class_id === classId );
    else if ( classIds.length > 0 )
      options = options.filter( soldier => classIds.includes( soldier.class_id ) );

    if ( schoolId )
      options = options.filter( soldier => soldier.school_id === schoolId );
    else if ( schoolIds.length > 0 )
      options = options.filter( soldier => schoolIds.includes( soldier.school_id ) );

    // map them to what react-select expects
    options = options.map( ({ user_id, first, last }) => ({ value: user_id, label: `${first} ${last}` }) );
    // add special options
    if ( showAllOption && !isMulti ) 
      options.unshift({ value: false, label: 'All Soldiers' });
    // and return the options
    return options;
  }

  onChange = ( option ) => {
    return this.props.onChange && this.props.onChange( option );
  }

  filter = ( option, value ) => option.label.toLowerCase().includes( value.toLowerCase() );
  
  render() {
    const { value, loading, values, showAllOption } = this.props;

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

const mapStateToProps = ({ base }) => {
  return {
    loading: base.soldiers.loading,
    soldiers: base.soldiers.soldiers,
  };
}

export default connect( mapStateToProps, { getSoldiers } )( SoldierSelect );
