import React, { useState } from 'react';
// components
import { Link } from 'react-router-dom';
import { FontAwesome, ButtonBar } from 'components/ui';
import { Alert, Input, Button, InputGroup, InputGroupText } from 'reactstrap';
// state
import { sendResetRequest } from 'store/login/operations';
// styles and images
import logo from 'img/logos/th.svg';
import { onInputChange } from 'functions/events';

const Forgot = () => {
  const [state, setState] = useState({
    email: '',
    error: '',
    notice: '',
    sending: false
  });
  const { email, error, notice, sending } = state;

  const handleChange = (updates) => {
    setState(prev => ({ ...prev, ...updates }));
  };

  const onChange = onInputChange(handleChange);

  const handleLoginForm = event => {
    event.preventDefault();
    setState(prev => ({ ...prev, sending: true }));
    // send the reset request to the server
    sendResetRequest(email)
      .then(notice => setState(prev => ({ ...prev, error: '', notice })))
      .catch(error => setState(prev => ({ ...prev, notice: '', error: error.message })))
      .then(() => setState(prev => ({ ...prev, sending: false, email: '' })));
  }

  return (
    <div id='Forgot'>
      <img src={logo} id='logo' alt='logo' />

      <h2>Reset Password</h2>

      <div className='form' id='forgot-form'>
        <form onSubmit={handleLoginForm}>
          <InputGroup size="lg">
            <div className="input-group-prepend">
              <InputGroupText>
                <FontAwesome icon='envelope' regular />
              </InputGroupText>
            </div>
            <Input
              name='email'
              type='email'
              tabIndex='1'
              value={email}
              autoFocus required
              onChange={onChange}
              placeholder='E-mail Address' />
          </InputGroup>

          <ButtonBar>
            <Link tabIndex='3' to='/'
              className='btn btn-primary btn-lg'>
              <FontAwesome icon='angle-double-left' /> Login
            </Link>

            <Button size="lg" tabIndex='2'
              color='primary' disabled={sending}>

              Send <FontAwesome spin={sending}
                icon={sending ? 'sync-alt' : 'angle-double-right'} />

            </Button>
          </ButtonBar>

          <Alert color='danger' isOpen={!!error}>{error}</Alert>

          <Alert color='success' isOpen={!!notice}>{notice}</Alert>

        </form>
      </div>
    </div>
  );
}

export default Forgot;
