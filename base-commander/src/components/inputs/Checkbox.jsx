import React from 'react';
import classnames from 'classnames';
import './styles/Checkbox.scss';

export const Checkbox = ( props ) => {
  const { setRef, className, children, ...inputProps } = props;
  // toggle on pressing enter
  let inputRef = null;
  const onKeyPress = ( event ) => {
    if ( event.key === 'Enter' ) {
      event.preventDefault(); // do not submit the parent form
      inputRef.click();
    }
  }
  // pass the inputRef up..
  const setupRef = ( ref ) => {
    inputRef = ref;
    if ( setRef ) { setRef( ref ) };
  }

  const classNames = classnames('checkbox', {
    [className]: className,
    'disabled': inputProps.disabled
  });

  return (
    <label className={ classNames } tabIndex={ inputProps.disabled ? -1 : 0 } onKeyPress={onKeyPress}>
      <input className='form-check-input' type='checkbox' { ...inputProps } ref={ ref => {setupRef( ref )}} />
      <span className='checkbox-state' />{' '}
      { children }
    </label>
  );
}
