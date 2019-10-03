import React from 'react';
import { ButtonGroup } from 'reactstrap';
import classnames from 'classnames';
import './styles/Design.scss';

export const ButtonBar = ({ children, className, ...props }) => {
  className = classnames('button-bar', { className: !!className });
  return (
    <ButtonGroup className={ className } {...props}>
      { children }
    </ButtonGroup>
  );
}
