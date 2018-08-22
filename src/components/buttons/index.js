import React from 'react';
import { FontAwesome } from 'components/ui';
import { Button, Collapse } from 'reactstrap';

export const SaveButton = ({ show = true, ...props }) => (
  <Collapse isOpen={ show } id='save'>
    <Button color='primary' { ...props }>
      <FontAwesome icon='save'/> Save Changes
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