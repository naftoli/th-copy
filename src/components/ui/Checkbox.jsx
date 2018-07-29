import React from 'react';
import './styles/Checkbox.scss';

const Checkbox = ( props ) => {
  const { onChange, checked, id, name, className } = props;
  const inputProps = { onChange, checked, id, name };
  return (
    <label className={`checkbox ${ className || '' }`}>
      <input className='form-check-input' type='checkbox' { ...inputProps } />
      <span className='checkbox-state' />{' '}
      { props.children }
    </label>
  );
}

export default Checkbox;
