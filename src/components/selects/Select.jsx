import React, { Component } from 'react';
// components
import ReactSelect from 'react-select';

function withDefaultProps( Select ){
  return class extends Component {
    render() {
      return (
        <Select openMenuOnFocus { ...this.props } classNamePrefix="react-select"/>
      )
    }
  }
}

export default withDefaultProps( ReactSelect );