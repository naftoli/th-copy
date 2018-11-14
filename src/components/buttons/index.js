import React from 'react';
import { FontAwesome, InlineSync } from 'components/ui';
import { Button, Collapse } from 'reactstrap';
// styles
import './styles.scss';

export * from './ChabadOrgButton';
export * from './GoogleButton';

export const SaveButton = ({
  show, children, text = 'Save Changes', saving = false, ...props 
}) => {
  const button = (
    <Button color='primary' { ...props }>
      {!saving && <span><FontAwesome icon='save'/> { children || text }</span> }
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
