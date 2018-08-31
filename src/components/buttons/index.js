import React from 'react';
import { FontAwesome, InlineSync } from 'components/ui';
import { Button, Collapse } from 'reactstrap';
// images
import { chabad, google } from 'img/logos';
// styles
import './styles.scss';

export const SaveButton = ({
  show = true, text = 'Save Changes', saving = false, ...props 
}) => (
  <Collapse isOpen={ show } id='save'>
    <Button color='primary' { ...props }>
      {!saving && <span><FontAwesome icon='save'/> { text }</span> }
      { saving && <span><InlineSync loading /> Saving...</span> }
    </Button>
  </Collapse>
);

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

export const ChabadOrgButton = props => {
  return (
    <Button className='ChabadOrgButton' color='primary' outline { ...props }>
      <img src={ chabad } alt='chabad.org' />
    </Button>
  );
}
