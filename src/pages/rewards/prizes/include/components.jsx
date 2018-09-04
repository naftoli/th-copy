import React from 'react';
import classnames from 'classnames';
import { Number } from 'components/ui';

export const Stock = ({ value, className, ...props }) => {
  let status;

  if ( value < 5 )
    status = 'low';
  else if ( value < 20 )
    status = 'warning';

  className = classnames( 'Stock', {
    [status]: !!status,
    [className]: !!className,
  });

  return <Number { ...props } className={ className } value={ value } />
}