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

class ParshaSelect extends Component {

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
    this.props.getParshos()
    .catch( e => toast.error( e.message ) );
  }

  componentDidUpdate() {
    const { value, isClearable, isMulti } = this.props;
    // if we have a value and it is not selected, select it
    const options = this.getOptions();
    const selected = findOption( options, value );
    if ( !selected && options.length > 0 && value === false ) {
      // if it is clearable and we have a value, clear it.
      if ( isClearable && value ) this.onChange( false );
      // if it is not clearable select the first value
      else if ( !isClearable && !isMulti ) this.onChange( options[0] );
    }
  }

  getOptions = () => {
    const { 
      parshos, endDate, startDate, isDescending
    } = this.props;

    let options = parshos;
    if ( options === undefined )
      return [];

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
