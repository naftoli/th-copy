import React from 'react';
// components
import { Account } from './Account';
import { PhoneNumber } from 'components/inputs';
import { GoogleButton, ChabadOrgButton } from 'components/buttons';
import { Row, Col, Input, Button } from 'reactstrap';
// import the chabad.org logo
import chabad from 'img/logos/chabad.png';
import google from 'img/logos/google.png';
// do nothing
const noop = () => {};
// generic disconnect button
const DisconnectButton = ({ onClick }) => {
  return (
    <Button outline color='danger' onClick={ onClick }>Disconnect</Button>
  )
}
// row to handle login information
export const LoginRow = props => {
  const { 
    username,     shliach_id,         google_id,
    editPassword, onConnect = noop,   onDisconnect = noop,  
  } = props;
  // chabad.org button defaults to disconnect
  let chabad_button = 
    <DisconnectButton onClick={ onDisconnect('chabad') } />;
  // google button defaults to disconnect
  let google_button = 
    <DisconnectButton onClick={ onDisconnect('google') } />;
  // if we are not connected to chabad.org then show the chabad.org button
  if ( !shliach_id )
    chabad_button = <ChabadOrgButton onLogin={ onConnect('chabad') } />;
  // if 
  if ( !google_id )
    google_button = <GoogleButton tokenOnly onSuccess={ onConnect('google') } />;

  return (
    <div className='LoginRow'>
      <Row>
        <Col sm={6}>
          <p><strong>Username & Password</strong></p>
          <p>{ username }</p>
        </Col>

        <Col sm={6}>
          <Button outline
              color='primary'
              onClick={ editPassword }>
            Change Username & Password
          </Button>
        </Col>
      </Row>

      <Row>
        <Col sm={6}>
          <img src={ chabad } alt='chabad.org' className='chabad-logo' />
          <p>{ shliach_id ? 'Connnected' : 'Not Connected' }</p>
        </Col>

        <Col sm={6}>
          { chabad_button }
        </Col>
      </Row>

      <Row>
        <Col sm={6}>
          <img src={ google } alt='google' className='chabad-logo' />
          <p>{ google_id ? 'Connnected' : 'Not Connected' }</p>
        </Col>

        <Col sm={6}>
          { google_button }
        </Col>
      </Row>
    </div>
  );
}

export const InformationRow = props => {
  const {
    last, title,  first,
    onChange,     admin_phone_work,
    admin_email,  admin_phone_mobile,
  } = props;
  return (
    <Row>
      <Col xs={12}>
        <label>E-Mail Address (unique)</label>
        <Input type='email'
          name='admin_email'
          value={ admin_email || '' }
          onChange={ onChange } />
        <div className='invalid-message'>
          Please enter a valid E-mail address
        </div>
      </Col>

      <Col xs={4} xl={3}>
        <label>Title</label>
        <Input 
          name='title' 
          value={ title || '' }
          onChange={ onChange } />
      </Col>

      <Col xs={8} xl={4}>
        <label>First Name</label>
        <Input required
          name='first'
          value={ first || '' } 
          onChange={ onChange } />
      </Col>

      <Col xs={12} xl={5}>
        <label>Last Name</label>
        <Input required
          name='last'
          value={ last || '' }
          onChange={ onChange } />
      </Col>

      <Col xs={12} sm={6}>
        <label>Work Phone</label>
        <PhoneNumber 
          onChange={ onChange }
          name='admin_phone_work'
          value={ admin_phone_work || '' } />
        <div className='invalid-message'>
          Please enter a valid phone number
        </div>
      </Col>

      <Col xs={12} sm={6}>
        <label>Cell Phone</label>
        <PhoneNumber
          onChange={ onChange }
          name='admin_phone_mobile'
          value={ admin_phone_mobile || '' } />
        <div className='invalid-message'>
          Please enter a valid phone number
        </div>
      </Col>
    </Row>
  );
}

export const AccessRow = props => {
  return (
    <div id='accounts'>
      { props.logins.map( ( login, index ) => 
        <Account 
          { ...login }
          key={ index }
          disconnect={ props.disconnect } />
      ) }
    </div>
  )
}
