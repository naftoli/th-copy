import React, { useState, useEffect } from 'react';
import { useDispatch } from 'react-redux';
// components
import { Password, Label } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import {
  Input, Row, Alert, Col, ModalBody,
  Modal, ModalHeader, ModalFooter,
} from 'reactstrap';
// state
import { updateCurrentUser } from 'store/login/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { onInputChange } from 'functions/events';
// style

const initialState = {
  error: false,
  saving: false,
  username: '',
  password: '',
  current_password: ''
};

const LoginModal = ({ isOpen, toggle, username: propsUsername }) => {
  const dispatch = useDispatch();

  const [state, setState] = useState({ ...initialState });
  const { saving, error, username, password, current_password } = state;

  useEffect(() => {
    setTitle('My Account');
  }, []);

  // * handle updates from the UI
  const handleUpdates = (updates) => {
    setState(prev => ({ ...prev, ...updates }));
  };

  // * actual event listener
  const onChange = onInputChange(handleUpdates);

  // * submit the event
  const onSubmit = event => {
    event.preventDefault();
    setState(prev => ({ ...prev, saving: true, error: false }));

    const updates = { username, password, current_password };

    dispatch(updateCurrentUser(updates))
      .then(() => {
        // update the state
        setState({ ...initialState });
        // show a notification
        let updated = 'Username & Password';
        if (!username) updated = 'Password';
        if (!password) updated = 'Username';
        toast.success(`${updated} Updated`);
      })
      .catch(e => setState(prev => ({ ...prev, saving: false, error: e.message })))
  }

  return (
    <Modal id='LoginModal' centered
      isOpen={isOpen} toggle={toggle}>

      <ModalHeader toggle={toggle}>
        Change Username & Password
      </ModalHeader>

      <form onSubmit={onSubmit}>
        <ModalBody>
          <Row>
            <Col xs={12}>
              <p>Your current password is required to change your username and password</p>
              <Label>Current Password</Label>
              <Password
                required
                tabToggle
                name='current_password'
                value={current_password}
                onChange={onChange}
                placeholder='Old Password' />
            </Col>
          </Row>

          <hr />

          <Row>
            <Col xs={12} sm={6}>
              <Label>New Username (unique)</Label>
              <Input
                name='username'
                value={username}
                autoComplete='username'
                onChange={onChange}
                placeholder={propsUsername} />
            </Col>

            <Col xs={12} sm={6}>
              <Label>New Password</Label>
              <Password
                tabToggle
                defaultOpen
                name='password'
                value={password}
                onChange={onChange}
                autoComplete='new-password'
                placeholder='New Password' />
            </Col>
          </Row>

          {error &&
            <Alert color='danger'>Error: {error}</Alert>}
        </ModalBody>

        <ModalFooter>

          <SaveButton
            saving={saving}
            disabled={!current_password || (!username && !password)} />

        </ModalFooter>
      </form>
    </Modal>
  );
}

export default LoginModal;
