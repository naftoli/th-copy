import React from 'react';
import classnames from 'classnames';

import { FontAwesome, InlineSync } from 'components/ui';
import { Button, Collapse } from 'reactstrap';
// styles
import './styles.scss';

export * from './ChabadOrgLegacy';
export * from './GoogleButton';

export const SaveButton = rawProps => {
  let {
    show, children, text = 'Save Changes', 
    saving = false, icon, className, ...props 
  } = rawProps;
  // set the classnames
  className = classnames({
    [className]: className,
    'SaveButton': true
  })
  // define the button
  const button = (
    <Button color='primary' className={ className } { ...props }>
      {!saving && <span><FontAwesome icon={ icon || 'save' }/> { children || text }</span> }
      { saving && <span><InlineSync loading /> Saving...</span> }
    </Button>
  );
  if ( show === undefined )
    return button;

  return (
    <Collapse isOpen={ show } id='save'>
      { button }
    </Collapse>
  );
}
