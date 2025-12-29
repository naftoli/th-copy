import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
// components
import { TabPane } from 'reactstrap';
import { isHQ } from 'functions/login';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { HQSettingsRow, SettingsRow } from '../rows';

export const SettingsTab = ({
  base,
  login,
  tabId,
  onUpdate,
  onSubmit,
  onValidChange,
  updated
}) => {
  return (
    <TabPane tabId={tabId}>
      <Form id='SettingsTab' onSubmit={onSubmit} onValidChange={onValidChange}>

        <SettingsRow
          base={base}
          onUpdate={onUpdate} />

        {isHQ(login.code) &&
          <Fragment>
            <p className='title'>HQ only settings</p>

            <HQSettingsRow
              base={base}
              onUpdate={onUpdate} />
          </Fragment>
        }

        <SaveButton show={updated} />

      </Form>
    </TabPane>
  )
}

SettingsTab.propTypes = {
  base: PropTypes.object.isRequired,
  login: PropTypes.object.isRequired,
  tabId: PropTypes.number.isRequired,
  onUpdate: PropTypes.func.isRequired,
  onSubmit: PropTypes.func.isRequired,
  onValidChange: PropTypes.func.isRequired,
};
