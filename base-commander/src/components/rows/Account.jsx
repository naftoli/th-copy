import React from 'react';

import { Label, PhoneNumber } from 'components/inputs';
import { Row, Col, Input } from 'reactstrap';

export const AccountRow = ({ account, onChange, xl = false }) => {
  return (
    <Row>
      <Col xs={4}>
        <Label>Title</Label>

        <Input name='title' 
          placeholder='Title'
          onChange={ onChange }
          value={ account.title || '' } />
      </Col>

      <Col xs={8}>
        <Label>First Name</Label>

        <Input
          required
          name='first'
          onChange={ onChange }
          placeholder='First Name'
          value={ account.first || '' } />
      </Col>

      <Col xs={12}>
        <Label>Last Name</Label>

        <Input
          required
          name='last'
          onChange={ onChange }
          placeholder='Last Name'
          value={ account.last || '' } />
      </Col>

      <Col xs={12} sm={6}>
        <Label>Work Phone</Label>

        <PhoneNumber
          required
          onChange={ onChange }
          name='admin_phone_work'
          placeholder='Work Phone'
          value={ account.admin_phone_work || '' } />

        <div className='invalid-message'>Please enter a valid phone number</div>
      </Col>

      <Col xs={12} sm={6}>
        <Label>Cell Phone</Label>

        <PhoneNumber
          required
          onChange={ onChange }
          placeholder='Cell Phone'
          name='admin_phone_mobile'
          value={ account.admin_phone_mobile || '' } />

        <div className='invalid-message'>Please enter a valid phone number</div>
      </Col>
    </Row>
  );
}
