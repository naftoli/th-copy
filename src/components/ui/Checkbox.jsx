import React from 'react';
import './styles/Checkbox.scss';

const Checkbox = ( props ) => {
  const { onChange, checked, id, name, className } = props;
  const inputProps = { onChange, checked, id, name };
  // toggle on pressing enter
  const ref = React.createRef();
  const onKeyPress = ( event ) => {
    if ( event.key === 'Enter' ) ref.current.click();
  }

  return (
    <label className={`checkbox ${ className || '' }`} tabIndex={0} onKeyPress={onKeyPress}>
      <input className='form-check-input' type='checkbox' { ...inputProps } ref={ref} />
      <span className='checkbox-state' />{' '}
      { props.children }
    </label>
  );
}

export default Checkbox;
