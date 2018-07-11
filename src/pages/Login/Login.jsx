import React, { Component } from 'react';
import { InputGroup, InputGroupAddon, Button } from 'reactstrap';
import Spinner from 'components/ui/Spinner.jsx';
import { connect } from 'react-redux';
import { login } from 'store/login/operations';

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

  togglePassword = () => {
    // update the state
    this.setState({
      show_password: !this.state.show_password
    }, () => {
      // update the feild without re-rendering the component ( If we have access to the dom )
      const password = document.querySelector('#login-form #password');
      if ( password ) {
        password.type = this.state.show_password ? 'text' : 'password';
        password.focus();
      }
    });
  }

  handleLoginForm = ( event ) => {
    event.preventDefault();
    this.setState( { show_password: false } ); // reset the show-password state
    this.props.login( this.state.username, this.state.password )
  }

  render(){
    let errors = this.props.errors.map( (error, index) => 
      <div className="alert alert-danger" key={index}>{error}</div> 
    );

    let form = (
      <form id="login-form" onSubmit={ this.handleLoginForm }>
        <InputGroup size="lg">
          <InputGroupAddon addonType="prepend">
            <img className="pt-icon" src={user} alt='username' width='26' height='26'/>
          </InputGroupAddon>
          <input className='form-control' placeholder='Username' autoFocus='true' required
            onChange={this.handleChange} value={this.state.username} id='username' />
        </InputGroup>
        <InputGroup size="lg">
          <InputGroupAddon addonType="prepend">
            <img className="pt-icon" src={lock} alt='username' width='26' height='26'/>
          </InputGroupAddon>
          <input className='form-control' placeholder='Password' type='password' required
            onChange={this.handleChange} value={this.state.password} id='password' />
          <InputGroupAddon addonType="append">
            <Button onClick={ this.togglePassword } id='toggle-password' 
              tabIndex="-1" outline>
              <i className={`far fa-${this.state.show_password ? 'eye' : 'eye-slash'}`}></i>
            </Button>
          </InputGroupAddon>
        </InputGroup>
        { errors }
        <Button size="lg" color='primary' type='submit'>
          Login <i className="fas fa-sign-in-alt"></i>
        </Button>
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

export default connect(mapStateToProps, { login })(Login);