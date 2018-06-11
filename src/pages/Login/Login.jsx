import React, { Component } from 'react';
import { InputGroup, Button } from '@blueprintjs/core';
import { connect } from 'react-redux';
import { operations } from 'store/login/actions';

import './Login.scss';
import logo from 'img/logo.svg';
import user from 'img/icons/profile-1-color-gray.svg';
import lock from 'img/icons/lock-color-gray.svg';

export class Login extends Component {

  constructor( props ){
    super( props );
    this.state = {
      username: '',
      password: '',
      show_password: false
    }
  }

  handleChange = ( event ) => {
    this.setState({
      [event.target.id]: event.target.value
    });
  }

  togglePassword = () => {
    this.setState({
      show_password: !this.state.show_password
    });
  }

  handleLoginForm = ( event ) => {
    event.preventDefault();
    this.props.login( this.state.username, this.state.password )
  }

  render(){
    const userIcon = <img className="pt-icon" src={user} alt='username' width='26' height='26'/>
    const lockIcon = <img className="pt-icon" src={lock} alt='password' width='26' height='26'/>
    const lockButton = <Button icon={ this.state.show_password ? 'eye-open' : 'eye-off'} minimal={true} onClick={ this.togglePassword } />
    return (
      <div id='login-page'>
        <img src={logo} id='logo' alt='logo' />
        <form id="login-form" onSubmit={ this.handleLoginForm }>
          <InputGroup 
            large={true} leftIcon={userIcon} placeholder="Username" 
            onChange={this.handleChange} value={this.state.username} id='username' />
          <InputGroup 
            large={true} leftIcon={lockIcon} placeholder="Password" 
            onChange={this.handleChange} value={this.state.password} id='password' 
            type={ this.state.show_password ? 'text' : 'password' } rightElement={ lockButton } />
          <Button text='Login' intent='primary' large={true} type='submit'/>
        </form>
      </div>
    );
  }
}

export default connect(false, { login: operations.login })(Login);