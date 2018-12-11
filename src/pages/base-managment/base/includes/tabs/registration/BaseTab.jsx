import React from 'react';

import { TabPane } from 'reactstrap';
import { BaseRow } from '../../rows';
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { NavigationRow } from '../../rows/registration/NavigationRow';

export class BaseTab extends React.Component {
  
  render(){
    const {
      tabId, base, onUpdate, onChange, onSubmit, onValidChange
    } = this.props;

    return (
      <TabPane tabId={ tabId } id='BaseTab'>

        <Form
          validateAfterSubmit
          onSubmit={ onSubmit }
          onValidChange={ onValidChange }>

          <BaseRow required
            onUpdate={ onUpdate }
            inst_id={ base.inst_id }
            school_name={ base.school_name }
            hachayol_name={ base.hachayol_name }
            school_name_he={ base.school_name_he } />

          <AddressRow
            required
            showPhone
            { ...base }
            title={ false }
            prefix='school_'
            onChange={ onChange } />

          <NavigationRow next />
        </Form>
      </TabPane>
    )
  }
}