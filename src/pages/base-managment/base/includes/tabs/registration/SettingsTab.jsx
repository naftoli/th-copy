import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { TabPane } from 'reactstrap';
import { SettingsRow } from '../../rows';
import { Form } from 'components/inputs';
import { RegTypeRow } from '../../rows/RegTypeRow';
import { NavigationRow } from '../../rows/registration/NavigationRow';

export class SettingsTab extends Component {

  static propTypes = {
    base: PropTypes.object.isRequired,
    tabId: PropTypes.number.isRequired,
    onUpdate: PropTypes.func.isRequired,
    onSubmit: PropTypes.func.isRequired,
    onValidChange: PropTypes.func,
  }

  render(){
    const { 
      onSubmit, base, tabId,
      onUpdate, back, onValidChange,
    } = this.props;

    let { child_fee, reg_type, earlyBird } = base;

    return (
      <TabPane tabId={ tabId }>
      
        <Form id='SettingsTab'
            validateAfterSubmit
            onSubmit={ onSubmit }
            onValidChange={ onValidChange }>

          <p className='title'>
            General Base Settings
          </p>

          <SettingsRow
            base={ base }
            onUpdate={ onUpdate } />

          <p className='title'>
            Registration Settings
          </p>

          <RegTypeRow
            regType={ reg_type }
            onUpdate={ onUpdate }
            childFee={ child_fee }
            earlyBird={ earlyBird }
            prices={ base.currentRegPrices } />

          <NavigationRow back={ back } next />
        </Form>
      </TabPane>
    )
  }
}
