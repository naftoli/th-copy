import React from 'react';
import { Alert } from 'reactstrap';
import './styles/Callout.scss';

const Callout = ( { icon, title, children, color='primary' } ) => {
  return (
    <Alert className='th-callout' color={color}>
      { icon && <i className={ icon }></i> }
      { title && <h4>{ title }</h4> }
      { children }
    </Alert>
  );
}

export default Callout;