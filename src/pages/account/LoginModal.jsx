import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Password, Label } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { 
  Input,  Row,    Col,  ModalBody,
  Modal,  ModalHeader,  ModalFooter,
} from 'reactstrap';
// state
import { updateCurrentUser } from 'store/login/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { onInputChange } from 'functions/events';
import { showError } from 'functions/notifications';
// style

const initialState = {
  saving: false,
  username: '',
  password: '',
  current_password: ''
};

class LoginModal extends Component {

  state = { ...initialState };

  componentDidMount() {
    setTitle( 'My Account' );
  }

  // * handle updates from the UI
  handleUpdates = updates => 
    this.setState({ ...updates });

  // * actual event listener
  onChange = onInputChange( this.handleUpdates );

  // * submit the event
  onSubmit = event => {
    event.preventDefault();
    this.setState({ saving: true });

    const { username, password, current_password } = this.state;
    const updates = { username, password, current_password };

    showError(
      this.props.updateCurrentUser( updates )
      .then( () => {
        // update the state
        this.setState({ ...initialState });
        // show a notification
        let updated = 'Username & Password';
        if ( !username )  updated = 'Password';
        if ( !password )  updated = 'Username';
        toast.success(`${ updated } Updated`);
      })
    ).then( () => this.setState({ saving: false }) );
  }

  render() {
    let { isOpen, toggle } = this.props; // load the account and the updates
    let { saving, username, password, current_password } = this.state;

    return (
      <Modal id='LoginModal'  centered
          isOpen={ isOpen }   toggle={ toggle }>
        <ModalHeader toggle={ toggle }>
          Change Username & Password
        </ModalHeader>

        <ModalBody>
          <Row>
            <Col xs={12}>
              <p>Your current password is required to change your username and password</p>
              <Label>Current Password</Label>
              <Password
                required
                tabToggle
                name='current_password'
                value={ current_password }
                onChange={ this.onChange }
                placeholder='Old Password' />
            </Col>
          </Row>

          <hr/>
          
          <Row>
            <Col xs={12} sm={6}>
              <Label>New Username (unique)</Label>
              <Input
                name='username' 
                value={ username }
                onChange={ this.onChange }
                placeholder={ this.props.username } />
            </Col>

            <Col xs={12} sm={6}>
              <Label>New Password</Label>
              <Password
                tabToggle
                defaultOpen
                name='password'
                value={ password }
                onChange={ this.onChange }
                placeholder='New Password' />
            </Col>
          </Row>
        </ModalBody>

        <ModalFooter>

          <SaveButton
            saving={ saving }
            onClick={ this.onSubmit }
            disabled={ saving || !current_password || ( !username && !password ) } />
        
        </ModalFooter>
      </Modal>
    );
  }
}

const mapDispatchToProps = {
  updateCurrentUser
};

export default connect( null, mapDispatchToProps )( LoginModal );
