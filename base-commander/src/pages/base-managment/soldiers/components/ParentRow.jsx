import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
// components
import {
  Row, Col, Input, InputGroup, InputGroupAddon, Button,
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

class ParentRow extends Component {
  // props we are expecting for this component
  static propTypes = {
    createAuth: PropTypes.func.isRequired,
    removeAuth: PropTypes.func.isRequired,
    updateParentCredentials: PropTypes.func.isRequired,
  }
  static defaultProps = {
    parentAccount: false, // object with first, last, phone, email and key props
    userId: false, // integer
  }
  // handle parent username input and credential management
  state = {
    username: '',
    modalOpen: false,
    newUsername: '',
    newPassword: '',
    confirmPassword: '',
    isSubmitting: false
  }

  usernameRef = React.createRef();

  updateUsername = e =>
    this.setState({ username: e.target.value });

  changeLogin = () =>
    mobileLogin( this.props.parentAccount.key );
    
  toggleCredentialModal = () => {
    const { parentAccount } = this.props;
    
    // Get the correct username from the parent account
    // The username could be in different properties depending on the data structure
    let username = '';
    if (parentAccount) {
      // Try different possible properties where username might be stored
      if (parentAccount.username) {
        username = parentAccount.username;
      } else if (parentAccount.email) {
        // Sometimes email is used as username
        username = parentAccount.email;
      }
      
      console.log('Opening modal with username:', username);
    }
    
    this.setState(prevState => ({
      modalOpen: !prevState.modalOpen,
      newUsername: prevState.modalOpen ? '' : username,
      newPassword: '',
      confirmPassword: '',
      isSubmitting: false
    }));
  }
  
  handleInputChange = (e) => {
    this.setState({ [e.target.name]: e.target.value });
  }
  
  updateCredentials = (e) => {
    e.preventDefault();
    const { newUsername, newPassword, confirmPassword } = this.state;
    const { parentAccount, updateParentCredentials } = this.props;
    
    // Validation
    if (!newUsername) {
      toast.error('Username is required');
      return;
    }
    
    if (newPassword && newPassword !== confirmPassword) {
      toast.error('Passwords do not match');
      return;
    }
    
    this.setState({ isSubmitting: true });
    
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
        this.setState({ isSubmitting: false });
        toast.success('Credentials updated successfully');
        this.toggleCredentialModal();
      })
      .catch(error => {
        this.setState({ isSubmitting: false });
        toast.error(error.message || 'Failed to update credentials');
        console.error('Error updating credentials:', error);
      });
  }

  addToAccount = () => {
    const auth = 'user';
    const { username } = this.state;
    const { userId: id, createAuth } = this.props;
    // make sure something was typed
    if ( username === '' && this.usernameRef.current ) 
      return this.usernameRef.current.focus();
    // valid emails we look for the email address
    if ( is.email( username ) )
      return showError( createAuth({ id, auth, email: username }) );
    // otherwise look for the username
    return showError( createAuth({ id, auth, username }) );
  }

  remove = () => {
    const { userId: id, parentAccount } = this.props;
    const admin_id = parseInt( parentAccount.admin_id, 10 );
    
    showError(
      this.props.removeAuth({ admin_id, id, auth: 'user' })
      // .then( this.props.refresh )
    );
  }

  render() {
    // Debug the parent account data
    console.log('ParentRow render - parentAccount:', this.props.parentAccount);
    
    if ( this.props.parentAccount ) {
      const { first, last, phone, email, username } = this.props.parentAccount;
      // Log the extracted username
      console.log('Extracted username:', username);
      return (
        <Row>
          <Col xs='12'>
            <label>Name</label>
            <Input disabled value={ first + ' ' + last } />
          </Col>
          <Col xs='12' sm='6'>
            <label>Phone Number</label>
            <Input disabled value={ phone } />
          </Col>
          <Col xs='12' sm='6'>
            <label>E-mail</label>
            <Input disabled value={ email } />
          </Col>
          
          <Col xs='12' sm='6' md='4' style={styles.buttonColumn}>
            <Button color='primary' onClick={ this.changeLogin }>
              Login to Parent Account
            </Button>
          </Col>
          <Col xs='12' sm='6' md='4' style={styles.buttonColumn}>
            <Button color='info' onClick={ this.toggleCredentialModal }>
              Change Credentials
            </Button>
          </Col>
          <Col xs='12' sm='6' md='4' style={styles.buttonColumn}>
            <Button color='danger' onClick={ this.remove }>
              Remove From Parent Account
            </Button>
          </Col>
          
          {/* Credential Change Modal */}
          <Modal isOpen={this.state.modalOpen} toggle={this.toggleCredentialModal}>
            <ModalHeader toggle={this.toggleCredentialModal}>Change Parent Account Credentials</ModalHeader>
            <Form onSubmit={this.updateCredentials}>
              <ModalBody>
                <FormGroup>
                  <Label for="newUsername">Username</Label>
                  <Input 
                    type="text" 
                    name="newUsername" 
                    id="newUsername" 
                    value={this.state.newUsername} 
                    onChange={this.handleInputChange} 
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
                    value={this.state.newPassword} 
                    onChange={this.handleInputChange} 
                    placeholder="Enter new password"
                  />
                </FormGroup>
                <FormGroup>
                  <Label for="confirmPassword">Confirm Password</Label>
                  <Input 
                    type="password" 
                    name="confirmPassword" 
                    id="confirmPassword" 
                    value={this.state.confirmPassword} 
                    onChange={this.handleInputChange} 
                    placeholder="Confirm new password"
                    disabled={!this.state.newPassword}
                  />
                </FormGroup>
              </ModalBody>
              <ModalFooter>
                <Button color="secondary" onClick={this.toggleCredentialModal} disabled={this.state.isSubmitting}>
                  Cancel
                </Button>
                <Button color="primary" type="submit" disabled={this.state.isSubmitting}>
                  {this.state.isSubmitting ? 'Saving...' : 'Save Changes'}
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
              <input onChange={this.updateUsername} value={this.state.username} 
                placeholder='Username / Email' ref={ this.usernameRef } className='form-control'/>
              <InputGroupAddon addonType="append">
                <Button onClick={ this.addToAccount } color='primary' outline tabIndex={0}>
                  <FontAwesome icon='user-plus' /> Add Soldier
                </Button>
              </InputGroupAddon>
            </InputGroup>
          </Col>
        </Row>
      );
    }
  }
}

// Connect component to Redux store
const mapDispatchToProps = {
  updateParentCredentials
};

export default connect(null, mapDispatchToProps)(ParentRow);
export { ParentRow };
