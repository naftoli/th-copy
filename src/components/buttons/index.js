import React from 'react';
import { FontAwesome, InlineSync } from 'components/ui';
import { Button, Collapse } from 'reactstrap';
// images
import { google } from 'img/logos';
// styles
import './styles.scss';

export * from './ChabadOrgButton';

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

export const ErrorButton = ({ show = true, ...props }) => (
  <Collapse isOpen={ show } id='save'>
    <Button color='danger' role='button' disabled {...props}>
      <FontAwesome icon='exclamation-circle'/> Cannot Save Invalid Information. 
        Please Check <strong>All</strong> Tabs.
    </Button>
  </Collapse>
);

export const GoogleButton = props => {
  return (
    <Button 
      className='GoogleButton' color='primary' outline 
      { ...props } role='button'>
      <img src={ google } alt='google'/> Sign In With Google
    </Button>
  );
}
