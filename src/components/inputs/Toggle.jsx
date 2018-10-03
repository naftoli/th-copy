import React from 'react';
import './styles/Toggle.scss';

export const Toggle = ( props ) => {
  let { className, setRef, on, off, ...inputProps } = props;
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

  return (
    <label className={`toggle ${ className || '' }`} onKeyPress={ onKeyPress }>
      <input type="checkbox" { ...inputProps } ref={ ref => { setupRef( ref ) } } />
      <div className="switchbox">
        <div className="switch" data-checked={ on || 'on' } data-unchecked={ off || 'off' }></div>
      </div>
    </label>
  );
}
