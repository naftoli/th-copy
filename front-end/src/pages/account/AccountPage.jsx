import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components

import { SaveButton } from 'components/buttons';
import { UnsavedChangesPrompt } from 'components/navigation';
import { LoginRow, InformationRow, AccessRow } from './includes/Rows';
// state
import { removeAuth } from 'store/base/staff/operations';
import {
  connectExternalAccount, getCurrentUser,
  disconnectExternalAccount, updateCurrentUser,
} from 'store/login/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { showError } from 'functions/notifications';
import { filterUpdates, onInputChange } from 'functions/events';
// style
import './includes/AccountPage.scss';
import LoginModal from './LoginModal';

const AccountPage = () => {
  const dispatch = useDispatch();
  const account = useSelector(state => state.login.current_user);

  const [updates, setUpdates] = useState({});
  const [saving, setSaving] = useState(false);
  const [isOpen, setIsOpen] = useState(false);

  useEffect(() => {
    setTitle('My Account');
    dispatch(getCurrentUser());
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  // * handle updates from the UI
  const handleUpdates = (newUpdates) => {
    // we do not load the password, so leave it blank by default
    const currentAccount = { ...account, password: '' }
    // filter updates for any changes
    const filteredUpdates = filterUpdates(currentAccount, { ...updates, ...newUpdates });
    // and update the state
    setUpdates(filteredUpdates);
  };
  const onChange = onInputChange(handleUpdates);

  // * submit the event
  const onSubmit = event => {
    event.preventDefault();
    // do not double submit
    if (saving)
      return true;
    // update the state
    setSaving(true);
    // update the user, showing any errors
    showError(
      dispatch(updateCurrentUser(updates))
        .then(() => setUpdates({}))
    )
      .then(() => setSaving(false));
  }

  // * toggle the modal
  const toggle = () => setIsOpen(!isOpen);

  // * disconnect logins
  const disconnectLogin = (auth, id) => {
    const { admin_id } = account;
    dispatch(removeAuth({ auth, id, admin_id }))
      .then(() => dispatch(getCurrentUser()));
  }

  // * connect to an external account
  const connectExternalAccountHandler = type => key => showError(
    dispatch(connectExternalAccount(type, key))
      .then(() => toast.info(`${type} account connected`))
  );

  // * disconnect from an external account
  const disconnectExternalAccountHandler = type => () => showError(
    dispatch(disconnectExternalAccount(type))
      .then(() => toast.info(`${type} account removed`))
  );

  let currentAccount = { ...account, ...updates };
  // and check if any of the updates require saving
  const updated = Object.keys(updates).length > 0;
  // extract props
  let {
    username, title, first, admin_email, last,
    logins, admin_phone_work, admin_phone_mobile,
    chabad_org_shliach_id, google_id
  } = currentAccount;

  // * only show some logins
  const filteredLogins = (logins || []).filter(
    login => ['INST', 'BC', 'TEACHER'].includes(login.code)
  );

  return (
    <div id='AccountPage'>
      <UnsavedChangesPrompt
        when={updated}
        message="You have unsaved changes. Are you sure you want to leave?"
      />

      <h1>My Tzivos Hashem Account</h1>

      <hr />

      <h4>Login Methods</h4>

      <LoginRow
        username={username}
        google_id={google_id}
        shliach_id={chabad_org_shliach_id}

        editPassword={toggle}
        onConnect={connectExternalAccountHandler}
        onDisconnect={disconnectExternalAccountHandler} />

      <hr />

      <h4>Account Information</h4>

      <form onSubmit={onSubmit}>
        <InformationRow
          first={first}
          last={last}
          title={title}
          onChange={onChange}
          admin_email={admin_email}
          admin_phone_work={admin_phone_work}
          admin_phone_mobile={admin_phone_mobile} />

        <SaveButton
          show={updated}
          saving={saving}
          disabled={saving} />
      </form>

      <hr />

      {filteredLogins.length > 0 &&
        <h4>Account Access</h4>
      }

      <AccessRow
        logins={filteredLogins}
        disconnect={disconnectLogin} />

      <LoginModal
        isOpen={isOpen}
        toggle={toggle}
        username={username} />

    </div>
  )
}

export default AccountPage;
