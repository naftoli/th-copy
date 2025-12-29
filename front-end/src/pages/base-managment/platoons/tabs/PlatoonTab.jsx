import React from 'react';
// components
import { TabPane } from 'reactstrap';
import { PlatoonRow } from './PlatoonRow';
import { Callout } from 'components/ui';

import { SaveButton } from 'components/buttons';
import { SettingsRow } from './SettingsRow';
import { onSelectChange, onCheckboxChange, onInputChange, onJSONChange } from 'functions/events';
// functions

export const PlatoonTab = ({ platoon, tabId, updated, onSubmit, onDelete, onUpdate }) => {

  // handle selects
  const onChange = onInputChange(onUpdate);
  const handleJSONChange = onJSONChange(onUpdate);
  const handleSelectChange = onSelectChange(onUpdate);
  const onCheckChange = onCheckboxChange(onUpdate);

  const inputProps = { onChange: onChange };
  const checkProps = { onChange: onCheckChange };

  return (
    <TabPane tabId={tabId} id='PlatoonTab'>

      <Callout title='Platoon Information'>
        <p>
          The teacher information below is the public facing information about this platoon.
          <strong> For example, the teacher name is used in the parent portal and the printed mission sheets. </strong>
        </p>
        <p>
          Please note that editing settings will edit the same settings on all soldiers if available.
        </p>
        <p>
          <strong>If you want to include 2 teachers for 1 class, you can write them both just divided by a ";"</strong>
        </p>
      </Callout>

      <form onSubmit={onSubmit}>

        <PlatoonRow
          platoon={platoon}
          onDelete={onDelete}
          inputProps={inputProps}
          onSelectChange={handleSelectChange} />

        <p className='title'>Platoon Settings</p>

        <SettingsRow
          platoon={platoon}
          inputProps={inputProps}
          checkProps={checkProps}
          onJSONChange={handleJSONChange} />

        <SaveButton show={updated} />

      </form>
    </TabPane>
  );
}
