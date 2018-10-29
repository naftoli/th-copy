import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { Select } from '../static/Select';
// functions
import { findOption } from 'functions/selects';

export class MissionTypeSelect extends Component {

  static propTypes = {
    value: PropTypes.any,
    gender: PropTypes.oneOf(['M', 'F']),
    onChange: PropTypes.func,
  }

  componentDidUpdate({ gender }) {
    // if the gender changes...
    if ( this.props.gender !== gender ) {
      // compute the correct value ( e.g. 12 becomes 13 )
      const option = this.getSelected( this.getOptions() );
      if ( option && option.value !== this.props.value )
        this.props.onChange( option ); // and update it
    }
  }

  getOptions = () => {
    const offset = this.props.gender === 'F' ? 1 : 0;
    return [
      { value:  2 + offset, label: 'Chabad' },
      { value: 12 + offset, label: 'Frum' },
      { value: 22 + offset, label: 'C-Kids' }
    ];
  }

  getSelected = options => {
    const ending = this.props.gender === 'F' ? 3 : 2;
    const option = parseInt( this.props.value / 10, 10 ) * 10;
    return findOption( options, option + ending );
  }
  
  render() {
    let options = this.getOptions();
    let selected = this.getSelected( options );

    return (
      <Select
        { ...this.props }
        value={ selected }
        options={ options } />
    );
  }
}
