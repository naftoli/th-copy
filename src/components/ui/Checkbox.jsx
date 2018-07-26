import React, { Component } from 'react';
import './styles/Checkbox.scss';

class Checkbox extends Component{
  // static defaultProps = { size: 10 }
  render() {
    const { onChange, checked, id, name, className } = this.props;
    const inputProps = { onChange, checked, id, name };
    return (
      <label className={`checkbox ${ className || '' }`}>
        <input className='form-check-input' type='checkbox' { ...inputProps } />
        <span className='checkbox-state' />{' '}
        { this.props.children }
      </label>
    );
  }
}

export default Checkbox;
