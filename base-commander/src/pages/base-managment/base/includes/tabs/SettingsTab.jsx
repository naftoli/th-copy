import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
// components
import { TabPane } from 'reactstrap';
import { isHQ } from 'functions/login';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { HQSettingsRow, SettingsRow } from '../rows';

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
            onUpdate={ onUpdate } />

          { isHQ( login.code ) && 
            <Fragment>
              <p className='title'>HQ only settings</p>

              <HQSettingsRow
                base={ base }
                onUpdate={ onUpdate } />
            </Fragment>
          }

          <SaveButton show={ updated } />

        </Form>
      </TabPane>
    )
  }
}
