import React, { Component } from 'react';
import { InputGroup, Button } from '@blueprintjs/core';
import Spinner from 'components/ui/Spinner.jsx';
import { connect } from 'react-redux';
import { operations } from 'store/login/actions';

import './Login.scss';
import logo from 'img/logo.svg';
import { user, lock } from 'img/icons';

export class Login extends Component {
  constructor( props ){
    super( props );
    this.state = {
      username: '',
      password: '',
      show_password: false
    }
  }

  static defaultProps = {
    loading: false,
    errors: [],
    login: () => {}
  }

  handleChange = ( event ) => {
    this.setState({
      [event.target.id]: event.target.value
    });
  }

  togglePassword = ( event ) => {
    this.setState({
      show_password: !this.state.show_password
    });
    const password = document.querySelector('#password');
    if ( password ) {
      password.focus(); password.click();
    }
  }

  handleLoginForm = ( event ) => {
    event.preventDefault();
    this.props.login( this.state.username, this.state.password )
  }

  render(){
    const userIcon = <img className="pt-icon" src={user} alt='username' width='26' height='26'/>
    const lockIcon = <img className="pt-icon" src={lock} alt='password' width='26' height='26'/>
    const lockButton = <Button icon={ this.state.show_password ? 'eye-open' : 'eye-off'} minimal={true} onClick={ this.togglePassword } id='toggle-password' tabIndex="-1"/>
    
    let errors = this.props.errors.map( (error, index) => 
      <div className="alert alert-danger" key={index}>{error}</div> 
    );

    let form = (
      <form id="login-form" onSubmit={ this.handleLoginForm }>
        <InputGroup 
          large={true} leftIcon={userIcon} placeholder="Username" 
          onChange={this.handleChange} value={this.state.username} id='username' />
        <InputGroup 
          large={true} leftIcon={lockIcon} placeholder="Password"
          onChange={this.handleChange} value={this.state.password} id='password' 
          type={ this.state.show_password ? 'text' : 'password' } rightElement={ lockButton } />
        { errors }
        <Button text='Login' intent='primary' large={true} type='submit'/>
      </form>
    );

    if ( this.props.loading ) form = <Spinner size={ 8 } />;
    
    return (
      <div id='login-page'>
        <img src={logo} id='logo' alt='logo' />
        { form }
      </div>
    );
  }
}

const mapStateToProps = ( state ) => ({
  loading: state.login.loading,
  errors: state.login.errors
});

export default connect(mapStateToProps, { login: operations.login })(Login);