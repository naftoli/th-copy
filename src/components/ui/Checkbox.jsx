import React from 'react';
import './styles/Checkbox.scss';

const Checkbox = ( props ) => {
  const { onChange, checked, id, name, className, setRef } = props;
  const inputProps = { onChange, checked, id, name };
  // toggle on pressing enter
  let inputRef = null;
  const onKeyPress = ( event ) => {
    if ( event.key === 'Enter' ) inputRef.click();
  }
  // pass the inputRef up..
  const setupRef = ( ref ) => {
    inputRef = ref;
    if ( setRef ) { setRef( ref ) };
  }

  return (
    <label className={`checkbox ${ className || '' }`} tabIndex={0} onKeyPress={onKeyPress}>
      <input className='form-check-input' type='checkbox' { ...inputProps } ref={ ref => {setupRef( ref )}} />
      <span className='checkbox-state' />{' '}
      { props.children }
    </label>
  );
}

export default Checkbox;
