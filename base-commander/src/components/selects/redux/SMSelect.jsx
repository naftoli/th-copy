import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { toast } from 'react-toastify';
import { findOption } from 'functions/selects';
// state
import { getMonths } from 'store/missions/shabbos_mevorchim/operations';
import { julianToMoment, julianToday } from 'functions/dates';

class SMSelect extends Component {

  static propTypes = {
    value: PropTypes.any,
    onChange: PropTypes.func,
  }

  componentDidMount(){ 
    this.props.getMonths()
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
      // if it is not clearable select the most recent value
      else if ( !isClearable && !isMulti ) {
        const today = julianToday();
        this.props.onChange( options.filter( opt => opt.value <= today ).pop() );
      }
    }
  }

  getOptions = () => {
    // map them to what react-select expects
    return this.props.months.map( ({ month, date }) => ({
      value: date,
      label: `${month} - ${ julianToMoment( date ).format( 'l' ) }`
    }) );
  }

  filter = ( option, value ) => option.label.toLowerCase().includes( value.toLowerCase() );
  
  render() {
    const { value, loading } = this.props;

    let options = this.getOptions();
    let selected;
    // support single value
    if ( value )
      selected = findOption( options, value ) || null;
    else if ( value === false )
      selected = false;

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

const mapStateToProps = ({ missions }) => ({
  ...missions.shabbos_mevorchim,
});

export default connect( mapStateToProps, { getMonths } )( SMSelect );
