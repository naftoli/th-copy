import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
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
      if ( isClearable && value ) this.props.onChange( false );
      // if it is not clearable select the first value
      else if ( !isClearable && !isMulti ) this.props.onChange( options[0] );
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
    return options.map( ({ id, name, start }) => ({
      value: id,
      label: `${name} - ${ moment( this.julianToGregorian(start) ).format( 'l' ) }`
    }) );
  }

  // convert julian date to gregorian date
  julianToGregorian = julianDate => {
    const jd = Math.floor(julianDate) + 0.5;
    const z = Math.floor(jd);
    const f = jd - z;

    let a;
    if (z < 2299161) {
      a = z;
    } else {
      const alpha = Math.floor((z - 1867216.25) / 36524.25);
      a = z + 1 + alpha - Math.floor(alpha / 4);
    }

    const b = a + 1524;
    const c = Math.floor((b - 122.1) / 365.25);
    const d = Math.floor(365.25 * c);
    const e = Math.floor((b - d) / 30.6001);

    const day = b - d - Math.floor(30.6001 * e) + f;
    const month = e < 14 ? e - 1 : e - 13;
    const year = month > 2 ? c - 4716 : c - 4715;

    // Format the date as yyyy-mm-dd
    const formattedDate = `${year}-${String(month).padStart(2, '0')}-${String(Math.floor(day)).padStart(2, '0')}`;

    return formattedDate;
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
