import React, { Component } from 'react';
// components
import { TabPane } from 'reactstrap';
import { PlatoonRow } from './PlatoonRow';
import { Callout } from 'components/ui';

import { SaveButton } from 'components/buttons';
import { SettingsRow } from './SettingsRow';
import { onSelectChange, onCheckboxChange, onInputChange } from 'functions/events';
// functions

export class PlatoonTab extends Component {
  
  // handle selects
  onChange = onInputChange( this.props.onUpdate );
  onSelectChange = onSelectChange( this.props.onUpdate );
  onCheckChange = onCheckboxChange( this.props.onUpdate );

  render(){
    const { platoon, tabId, updated, onSubmit, onDelete } = this.props;

    const inputProps = { onChange: this.onChange };
    const checkProps = { onChange: this.onCheckChange };

    return (
      <TabPane tabId={ tabId } id='PlatoonTab'>

        <Callout title='Platoon Information'>
          <p>
            The teacher information below is the public facing information about this platoon.
            <strong> For example, the teacher name is used in the parent portal and the printed mission sheets. </strong>
          </p>
          <p>
            Please note that editing settings will edit the same settings on all soldiers if available.
          </p>
        </Callout>

        <form onSubmit={ onSubmit }>

          <PlatoonRow 
            platoon={ platoon } 
            onDelete={ onDelete } 
            inputProps={ inputProps }
            onSelectChange={ this.onSelectChange } />

          <p className='title'>Platoon Settings</p>

          <SettingsRow
            platoon={ platoon } 
            inputProps={ inputProps }
            checkProps={ checkProps } />

          <SaveButton show={ updated } />

        </form>
      </TabPane>
    );
  }
}
