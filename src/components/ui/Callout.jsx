import React from 'react';
import { Alert } from 'reactstrap';
import classnames from 'classnames';
import './styles/Callout.scss';

const Callout = ( { icon = 'fas fa-info-circle', title, children, color='primary' } ) => {
  const classNames = classnames('th-callout', {
    'th-callout-icon': !!icon
  });
  return (
    <Alert className={classNames} color={color} transition={{ baseClass: '', timeout: 0 }}>
      { icon && <i className={ icon }></i> }
      { title && <h4>{ title }</h4> }
      { children }
    </Alert>
  );
}

export default Callout;