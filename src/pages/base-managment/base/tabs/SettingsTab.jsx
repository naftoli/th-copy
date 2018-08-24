import React, { Component } from 'react';
// components
import { TabPane } from 'reactstrap';
import { SaveButton } from 'components/buttons';
import { Form } from 'components/inputs';
import { SettingsRow } from '../rows';
export class SettingsTab extends Component {

  render(){
    const { 
      updated, tabId, base, onSubmit, onValidChange, onUpdate
    } = this.props;

    return (
      <TabPane tabId={ tabId }>
        <Form id='SettingsTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>

          <SettingsRow
            base={ base }
            onUpdate={ onUpdate } />

          <SaveButton show={ updated } />
        </Form>
      </TabPane>
    )
  }
}
