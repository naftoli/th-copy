import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import {
  Row, Col, Input, InputGroup, InputGroupAddon, Button
} from 'reactstrap';
import { FontAwesome } from 'components/ui';
// functions
import is from 'is_js';
import { mobileLogin } from 'functions/login';
import { showError } from 'functions/notifications';

const styles = {
  buttonColumn: {
    marginTop: '.7em',
    textAlign: 'center'
  }
}

export class ParentRow extends Component {
  // props we are expecting for this component
  static propTypes = {
    createAuth: PropTypes.func.isRequired,
    removeAuth: PropTypes.func.isRequired,
  }
  static defaultProps = {
    parentAccount: false, // object with first, last, phone, email and key props
    userId: false, // integer
  }
  // handle parent username input
  state = {
    username: ''
  }

  usernameRef = React.createRef();

  updateUsername = e =>
    this.setState({ username: e.target.value });

  changeLogin = () =>
    mobileLogin( this.props.parentAccount.key );

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
    if ( this.props.parentAccount ) {
      const { first, last, phone, email } = this.props.parentAccount;
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
          <Col xs='12' sm='6' style={styles.buttonColumn}>
            <Button color='primary' onClick={ this.changeLogin }>
              Login to Parent Account
            </Button>
          </Col>
          <Col xs='12' sm='6' style={styles.buttonColumn}>
            <Button color='danger' onClick={ this.remove }>
              Remove From Parent Account
            </Button>
          </Col>
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
