import React, { Component } from 'react';
// components
import { Link } from 'react-router-dom';
import { FontAwesome, ButtonBar } from 'components/ui';
import { Alert, Input, Button, InputGroup, InputGroupAddon } from 'reactstrap';
// state
import { sendResetRequest } from 'store/login/operations';
// styles and images
import logo from 'img/logos/th.svg';
import { onInputChange } from 'functions/events';

export class Forgot extends Component {

  state = {
    email: '',
    error: '',
    notice: '',
    sending: false
  }

  static defaultProps = {
    // login: () => {}
  }

  handleChange = updates =>
    this.setState({ ...updates });

  onChange = onInputChange( this.handleChange );

  handleLoginForm = event => {
    event.preventDefault();
    this.setState({ sending: true })
    // send the reset request to the server
    sendResetRequest( this.state.email )
    .then( notice => this.setState({ error: '', notice }) )
    .catch( error => this.setState({ notice: '', error: error.message }) )
    .then( () => this.setState({ sending: false, email: '' }));
  }

  render(){
    let { error, email, notice, sending } = this.state;
    
    return (
      <div id='Forgot'>
        <img src={logo} id='logo' alt='logo' />

        <h2>Reset Password</h2>

        <div className='form' id='forgot-form'>
          <form onSubmit={ this.handleLoginForm }>
            <InputGroup size="lg">
              <InputGroupAddon addonType="prepend">
                <FontAwesome icon='envelope' regular/>
              </InputGroupAddon>
              <Input
                name='email' 
                type='email'
                tabIndex='1'
                value={ email }
                autoFocus required
                onChange={ this.onChange }
                placeholder='E-mail Address' />
            </InputGroup>

            <ButtonBar>
              <Link tabIndex='3' to='/'
                  className='btn btn-primary btn-lg'>
                <FontAwesome icon='angle-double-left'/> Login
              </Link>

              <Button size="lg"   tabIndex='2'  
                  color='primary' disabled={ sending }>

                Send <FontAwesome spin={ sending } 
                  icon={ sending ? 'sync-alt' : 'angle-double-right' } />

              </Button>
            </ButtonBar>

            <Alert color='danger' isOpen={ !!error }>{ error }</Alert>

            <Alert color='success' isOpen={ !!notice }>{ notice }</Alert>

          </form>
        </div>
      </div>
    );
  }
}

export default Forgot;
