import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from './Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getSoldiers } from 'store/base/soldiers/operations';

export class SoldierSelect extends Component {

  static propTypes = {
    onChange: PropTypes.func,
    value: PropTypes.any,

    classId: PropTypes.any,
    classIds: PropTypes.array,

    schoolId: PropTypes.any,
  }

  static defaultProps = {
    showAllOption: false,
    registeredOnly: false
  }

  componentDidMount(){ 
    this.loadSoldiers(); 
  }

  loadSoldiers = () => {
    return this.props.getSoldiers()
    .catch( e => toast.error( e.message ) );
  }

  getOptions = () => {
    const { 
      showAllOption, isMulti, 
      schoolId, registeredOnly, 
      classId, classIds, soldiers 
    } = this.props;
    
    if ( !schoolId )
      return [];

    let options = soldiers;

    // only soldiers in the classId from props
    if ( classId )
      options = options.filter( soldier => soldier.class_id === classId );
    else if ( classIds && classIds.length > 0 )
      options = options.filter( soldier => classIds.includes( soldier.class_id ) );
    else
      options = options.filter( soldier => !!soldier.class_id );
    // limit to school ID
    if ( schoolId )
      options = options.filter( soldier => soldier.school_id === schoolId );

    // limit to registered only
    if ( registeredOnly )
      options = options.filter( soldier => !!soldier.user_registered );

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
