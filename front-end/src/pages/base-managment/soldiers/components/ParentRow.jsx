import React, { useState, useRef } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
// components
import {
  Row, Col, Input, InputGroup, Button,
  Modal, ModalHeader, ModalBody, ModalFooter, FormGroup, Label, Form
} from 'reactstrap';
import { FontAwesome } from 'components/ui';
// functions
import is from 'is_js';
import { mobileLogin } from 'functions/login';
import { showError } from 'functions/notifications';
import { toast } from 'react-toastify';
// redux actions
import { updateParentCredentials } from 'store/base/parents/operations';

const styles = {
  buttonColumn: {
    marginTop: '.7em',
    textAlign: 'center'
  }
}

const ParentRowComponent = ({
  createAuth,
  removeAuth,
  updateParentCredentials,
  parentAccount = false,
  userId = false
}) => {
  const [username, setUsername] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [newUsername, setNewUsername] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const usernameRef = useRef();

  const handleUpdateUsername = e =>
    setUsername(e.target.value);

  const changeLogin = () =>
    mobileLogin(parentAccount.key);

  const toggleCredentialModal = () => {
    // Determine the initial username when opening the modal
    let initialUsername = '';
    if (!modalOpen && parentAccount) { // If opening
      // Try different possible properties
      if (parentAccount.username) {
        initialUsername = parentAccount.username;
      } else if (parentAccount.email) {
        initialUsername = parentAccount.email;
      }
      console.log('Opening modal with username:', initialUsername);
      setNewUsername(initialUsername);
    } else {
      setNewUsername(''); // or keep it? Original code reset to '' on CLOSE, and set on OPEN.
      // actually original code: 
      // modalOpen: !prevState.modalOpen
      // newUsername: prevState.modalOpen ? '' : username
      // If prev was open (now closing), set to empty. If prev was closed (now opening), set to username.
    }

    // Reset other fields
    if (!modalOpen) { // Opening
      setNewPassword('');
      setConfirmPassword('');
      setIsSubmitting(false);
    }

    setModalOpen(!modalOpen);
  }

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    if (name === 'newUsername') setNewUsername(value);
    if (name === 'newPassword') setNewPassword(value);
    if (name === 'confirmPassword') setConfirmPassword(value);
  }

  const updateCredentials = (e) => {
    e.preventDefault();

    // Validation
    if (!newUsername) {
      toast.error('Username is required');
      return;
    }

    if (newPassword && newPassword !== confirmPassword) {
      toast.error('Passwords do not match');
      return;
    }

    setIsSubmitting(true);

    // Prepare data for Redux action
    const data = {
      admin_id: parentAccount.admin_id,
      username: newUsername
    };

    // Only include password if it was changed
    if (newPassword) {
      data.password = newPassword;
    }

    // Dispatch Redux action to update credentials
    updateParentCredentials(data)
      .then(response => {
        setIsSubmitting(false);
        toast.success('Credentials updated successfully');
        toggleCredentialModal();
      })
      .catch(error => {
        if (error && error.isCanceled) return;
        setIsSubmitting(false);
        toast.error(error.message || 'Failed to update credentials');
        console.error('Error updating credentials:', error);
      });
  }

  const addToAccount = () => {
    const auth = 'user';
    // make sure something was typed
    if (username === '' && usernameRef.current)
      return usernameRef.current.focus();
    // valid emails we look for the email address
    if (is.email(username))
      return showError(createAuth({ id: userId, auth, email: username }));
    // otherwise look for the username
    return showError(createAuth({ id: userId, auth, username }));
  }

  const remove = () => {
    const admin_id = parseInt(parentAccount.admin_id, 10);

    showError(
      removeAuth({ admin_id, id: userId, auth: 'user' })
      // .then( refresh )
    );
  }

  // Debug parent account data
  console.log('ParentRow render - parentAccount:', parentAccount);

  if (parentAccount) {
    const { first, last, phone, email, username: pUsername } = parentAccount;
    // Log the extracted username
    console.log('Extracted username:', pUsername);
    return (
      <Row>
        <Col xs='12'>
          <label>Name</label>
          <Input disabled value={first + ' ' + last} />
        </Col>
        <Col xs='12' sm='6'>
          <label>Phone Number</label>
          <Input disabled value={phone} />
        </Col>
        <Col xs='12' sm='6'>
          <label>E-mail</label>
          <Input disabled value={email} />
        </Col>

        <Col xs='12' sm='6' md='4' style={styles.buttonColumn}>
          <Button color='primary' onClick={changeLogin}>
            Login to Parent Account
          </Button>
        </Col>
        <Col xs='12' sm='6' md='4' style={styles.buttonColumn}>
          <Button color='info' onClick={toggleCredentialModal}>
            Change Credentials
          </Button>
        </Col>
        <Col xs='12' sm='6' md='4' style={styles.buttonColumn}>
          <Button color='danger' onClick={remove}>
            Remove From Parent Account
          </Button>
        </Col>

        {/* Credential Change Modal */}
        <Modal isOpen={modalOpen} toggle={toggleCredentialModal}>
          <ModalHeader toggle={toggleCredentialModal}>Change Parent Account Credentials</ModalHeader>
          <Form onSubmit={updateCredentials}>
            <ModalBody>
              <FormGroup>
                <Label for="newUsername">Username</Label>
                <Input
                  type="text"
                  name="newUsername"
                  id="newUsername"
                  value={newUsername}
                  onChange={handleInputChange}
                  placeholder="Enter username"
                  required
                />
              </FormGroup>
              <FormGroup>
                <Label for="newPassword">New Password (leave blank to keep current)</Label>
                <Input
                  type="password"
                  name="newPassword"
                  id="newPassword"
                  value={newPassword}
                  onChange={handleInputChange}
                  placeholder="Enter new password"
                />
              </FormGroup>
              <FormGroup>
                <Label for="confirmPassword">Confirm Password</Label>
                <Input
                  type="password"
                  name="confirmPassword"
                  id="confirmPassword"
                  value={confirmPassword}
                  onChange={handleInputChange}
                  placeholder="Confirm new password"
                  disabled={!newPassword}
                />
              </FormGroup>
            </ModalBody>
            <ModalFooter>
              <Button color="secondary" onClick={toggleCredentialModal} disabled={isSubmitting}>
                Cancel
              </Button>
              <Button color="primary" type="submit" disabled={isSubmitting}>
                {isSubmitting ? 'Saving...' : 'Save Changes'}
              </Button>
            </ModalFooter>
          </Form>
        </Modal>
      </Row>
    );
  } else { // if we do not have a parent account
    return (
      <Row>
        <Col xs='12'>
          <label>Add to parent account by parents username / email</label>
          <InputGroup>
            <input onChange={handleUpdateUsername} value={username}
              placeholder='Username / Email' ref={usernameRef} className='form-control' />
            <Button onClick={addToAccount} color='primary' outline tabIndex={0}>
              <FontAwesome icon='user-plus' /> Add Soldier
            </Button>
          </InputGroup>
        </Col>
      </Row>
    );
  }
}

// Connect component to Redux store
// Reassign to match export
export const ParentRow = connect(null, { updateParentCredentials })(ParentRowComponent);

// Connect component to Redux store
const mapDispatchToProps = {
  updateParentCredentials
};

// export default connect(null, mapDispatchToProps)(ParentRow);
// export { ParentRow };
export default ParentRow;
