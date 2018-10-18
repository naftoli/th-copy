import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from './Select';
// functions
import moment from 'moment';
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getParshos } from 'store/missions/parshos/operations';

export class ParshaSelect extends Component {

  static propTypes = {
    value: PropTypes.any,
    values: PropTypes.array,
    onChange: PropTypes.func,
  }

  static defaultProps = {
    endDate: false,
    startDate: false,
    isDescending: false
  }

  componentDidMount(){ 
    this.loadSoldiers(); 
  }

  loadSoldiers = () => {
    return this.props.getParshos()
    .catch( e => toast.error( e.message ) );
  }

  getOptions = () => {
    const { 
      parshos, endDate, startDate, isDescending
    } = this.props;

    let options = parshos;

    // if an endDate is provided, find all parshos that start by that date
    if ( endDate )
      options = options.filter( parsha => parsha.end < endDate );
    // if a startDate is provided, find all parshos end on that date or later.
    if ( startDate )
      options = options.filter( parsha => parsha.start > startDate )

    if ( isDescending )
      options.reverse();

    // map them to what react-select expects
    return options.map( ({ id, name, start_date }) => ({
      value: id,
      label: `${name} - ${ moment( start_date ).format( 'l' ) }`
    }) );
  }

  onChange = ( option ) => {
    return this.props.onChange && this.props.onChange( option );
  }

  filter = ( option, value ) => option.label.toLowerCase().includes( value.toLowerCase() );
  
  render() {
    const { value, loading, isMulti, values } = this.props;

    let options = this.getOptions();
    let selected;
    // support single value
    if ( value )
      selected = findOption( options, value ) || null;
    else if ( value === false )
      selected = false;
    // support multiple values
    if ( isMulti && values )
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

const mapStateToProps = ({ missions }) => {
  return {
    loading: missions.parshos.loading,
    parshos: missions.parshos.parshos,
  };
}

export default connect( mapStateToProps, { getParshos } )( ParshaSelect );
