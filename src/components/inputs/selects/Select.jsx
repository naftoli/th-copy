import React, { Component } from 'react';
// components
import ReactSelect from 'react-select';
import ReactSelectCreatable from 'react-select/lib/Creatable';

function withDefaultProps( Select ){
  return class extends Component {
    render() {
      return (
        <Select 
          openMenuOnFocus
          { ...this.props }
          menuPlacement="auto" 
          classNamePrefix="react-select" />
      );
    }
  }
}

export const Select = withDefaultProps( ReactSelect );
export const Creatable = withDefaultProps( ReactSelectCreatable );