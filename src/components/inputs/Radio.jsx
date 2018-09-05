import React from 'react';
import './styles/Radio.scss';

export const Radio = ( props ) => {
  const { onChange, checked, id, name, value, className } = props;
  const inputProps = { onChange, checked, id, name, value };
  // toggle on pressing enter
  const ref = React.createRef();
  const onKeyPress = ( event ) => {
    if ( event.key === 'Enter' ) {
      event.preventDefault(); // do not submit the parent form
      ref.click();
    }
  }

  return (
    <label className={`radio ${ className || '' }`} tabIndex={0} onKeyPress={onKeyPress}>
      <input className='form-check-input' type='radio' { ...inputProps } ref={ref} />
      <span className='radio-state' />{' '}
      { props.children }
    </label>
  );
}
