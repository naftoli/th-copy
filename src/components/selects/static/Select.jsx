import React, { Component } from 'react';
// components
import ReactSelect from 'react-select';
import makeAnimated from 'react-select/lib/animated';
import ReactSelectCreatable from 'react-select/lib/Creatable';

function withDefaultProps( Select ){
  return class extends Component {

    state = { value: this.props.value || '' }

    selectRef = React.createRef();

    onChange = ( value, actionMeta ) => {
      this.props.onChange( value, actionMeta );
      this.setState({ value });
    }

    getValue = () => {
      const { value } = this.props;
      if ( value && value.value !== undefined)
        return value.value;
      if ( value !== undefined)
        return value;
      return this.state.value || '';
    };

    render() {
      const { required, id, ...props } = this.props;
      const { isDisabled } = this.props;
      const enableRequired = required && !isDisabled;

      return (
        <div className='Select' style={{ flexGrow: 1 }}>
          <Select
            { ...props }
            openMenuOnFocus
            menuPlacement='auto'
            ref={ this.selectRef }
            components={ makeAnimated() }
            classNamePrefix='react-select'
            id={ enableRequired ? undefined : id } />

          { enableRequired && 
            <input 
              required
              id={ id }
              tabIndex={ -1 }
              autoComplete='off'
              onChange={ () => {} }
              value={ this.getValue() }
              style={{
                opacity: 0, width: '100%',
                height: 0,  position: 'absolute'
              }}
              onFocus={ () => 
                this.selectRef.current && 
                this.selectRef.current.focus() } />
          }
        </div>
      );
    }
  }
}

export const Select = withDefaultProps( ReactSelect );
export const Creatable = withDefaultProps( ReactSelectCreatable );