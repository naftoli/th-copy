import React from 'react';
import classnames from 'classnames';
import './styles/Toggle.scss';

export const Toggle = ( props ) => {
  let { className, noAnimate, setRef, on, off, ...inputProps } = props;
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

  const classNames = classnames({
    'toggle': true,
    'no-animate': noAnimate,
    [className]: className
  })

  return (
    <label className={ classNames } onKeyPress={ onKeyPress }>
      <input type="checkbox" { ...inputProps } ref={ ref => { setupRef( ref ) } } />
      <div className="switchbox">
        <div className="switch" data-checked={ on || 'on' } data-unchecked={ off || 'off' }></div>
      </div>
    </label>
  );
}
