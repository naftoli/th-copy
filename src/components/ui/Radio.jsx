import React from 'react';
import './styles/Radio.scss';

const Radio = ( props ) => {
  const { onChange, checked, id, name, value, className } = props;
  const inputProps = { onChange, checked, id, name, value };
  return (
    <label className={`radio ${ className || '' }`}>
      <input className='form-check-input' type='radio' { ...inputProps } />
      <span className='radio-state' />{' '}
      { props.children }
    </label>
  );
}

export default Radio;
