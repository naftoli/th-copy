import React, { Component } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import { PhoneNumber, Password } from 'components/inputs';

export const LoginRow = ( props ) => {
  const { onChange, username, password, old_password } = props;
  return (
    <Row>
      <Col xs={12} sm={12} xl={4}>
        <label>Username</label>
        <Input required
          name='username' 
          value={ username }
          onChange={ onChange } />
      </Col>

      <Col xs={12} sm={6} xl={4}>
        <label>Current Password</label>
        <Password
          tabToggle
          defaultOpen
          name='old_password'
          onChange={ onChange }
          value={ old_password }
          placeholder='Old Password' />
      </Col>

      <Col xs={12} sm={6} xl={4}>
        <label>New Password</label>
        <Password
          tabToggle
          name='password'
          value={ password }
          onChange={ onChange }
          disabled={ !old_password }
          placeholder='New Password' />
      </Col>
    </Row>
  );
}

export const AccountRow = ( props ) => {
  const {
    last, title,  first,
    onChange,     admin_phone_work,
    admin_email,  admin_phone_mobile,
  } = props;
  return (
    <Row>
      <Col xs={4} xl={3}>
        <label>Title</label>
        <Input 
          name='title' 
          value={ title }
          onChange={ onChange } />
      </Col>

      <Col xs={8} xl={4}>
        <label>First Name</label>
        <Input required
          name='first'
          value={ first } 
          onChange={ onChange } />
      </Col>

      <Col xs={12} xl={5}>
        <label>Last Name</label>
        <Input required
          name='last'
          value={ last }
          onChange={ onChange } />
      </Col>

      <Col xs={12}>
        <label>E-Mail Address</label>
        <Input type='email'
          name='admin_email'
          value={ admin_email }
          onChange={ onChange } />
        <div className='invalid-message'>
          Please enter a valid E-mail address
        </div>
      </Col>

      <Col xs={12} sm={6}>
        <label>Work Phone</label>
        <PhoneNumber 
          onChange={ onChange }
          name='admin_phone_work'
          value={ admin_phone_work } />
        <div className='invalid-message'>
          Please enter a valid phone number
        </div>
      </Col>

      <Col xs={12} sm={6}>
        <label>Cell Phone</label>
        <PhoneNumber
          onChange={ onChange }
          name='admin_phone_mobile'
          value={ admin_phone_mobile } />
        <div className='invalid-message'>
          Please enter a valid phone number
        </div>
      </Col>
    </Row>
  );
}
