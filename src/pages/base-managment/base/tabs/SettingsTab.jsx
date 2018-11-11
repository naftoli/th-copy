import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { TabPane } from 'reactstrap';
import { SettingsRow } from '../rows';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';

export class SettingsTab extends Component {

  static propTypes = {
    base: PropTypes.object.isRequired,
    login: PropTypes.object.isRequired,
    tabId: PropTypes.number.isRequired,
    onUpdate: PropTypes.func.isRequired,
    onSubmit: PropTypes.func.isRequired,
    onValidChange: PropTypes.func.isRequired,
  }

  render(){
    const { 
      updated, tabId, base,   onSubmit, 
      onValidChange,  login,  onUpdate,
    } = this.props;

    return (
      <TabPane tabId={ tabId }>
        <Form id='SettingsTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>

          <SettingsRow
            base={ base }
            login={ login }
            onUpdate={ onUpdate } />

          <SaveButton show={ updated } />

        </Form>
      </TabPane>
    )
  }
}
