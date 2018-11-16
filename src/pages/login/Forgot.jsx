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
    notice: ''
  }

  static defaultProps = {
    // login: () => {}
  }

  handleChange = updates =>
    this.setState({ ...updates });

  onChange = onInputChange( this.handleChange );

  handleLoginForm = event => {
    event.preventDefault();
    // send the reset request to the server
    sendResetRequest( this.state.email )
    .then( notice => this.setState({ error: '', notice }) )
    .catch( error => this.setState({ notice: '', error: error.message }) );
  }

  render(){
    let { error, email, notice } = this.state;
    
    return (
      <div id='Forgot'>
        <img src={logo} id='logo' alt='logo' />

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
              <Link tabIndex='3' className='btn btn-primary btn-lg' to='/'>
                <FontAwesome icon='angle-double-left'/> Login
              </Link>

              <Button tabIndex='2' size="lg" color='primary'>
                Send <FontAwesome icon='angle-double-right'/>
              </Button>
            </ButtonBar>

            { error &&
              <Alert color='danger'>{ error }</Alert>
            }

            { notice &&
              <Alert color='success'>{ notice }</Alert>
            }
          </form>
        </div>
      </div>
    );
  }
}

export default Forgot;
