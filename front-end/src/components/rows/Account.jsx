import React, { useState } from 'react';

import { Label, PhoneNumber } from 'components/inputs';
import { Row, Col, Input, FormFeedback } from 'reactstrap';

export const AccountRow = ({ account, onChange, xl = false }) => {
  const [touched, setTouched] = useState({});

  const onBlur = (field) => () => {
    setTouched(prev => ({ ...prev, [field]: true }));
  };

  const isInvalid = (field, value) => {
    // Basic required check since pattern isn't explicit here, relies on required prop
    // AccountRow usage shows 'required' on fields. 
    // If value is empty and touched -> invalid.
    return touched[field] && !value; // simplistic check matching 'required'
  };

  // Phone validation is usually length 10+? PhoneNumber component might handle it?
  // Assuming PhoneNumber internal validation or simple required for now.

  return (
    <Row>
      <Col xs={4}>
        <Label>Title</Label>

        <Input name='title'
          placeholder='Title'
          onChange={onChange}
          value={account.title || ''} />
      </Col>

      <Col xs={8}>
        <Label>First Name</Label>

        <Input
          required
          name='first'
          onChange={onChange}
          onBlur={onBlur('first')}
          placeholder='First Name'
          valid={touched.first && !!account.first}
          invalid={touched.first && !account.first}
          value={account.first || ''} />
        <FormFeedback>First Name is required</FormFeedback>
      </Col>

      <Col xs={12} sm={6}>
        <Label>Last Name</Label>

        <Input
          required
          name='last'
          onChange={onChange}
          onBlur={onBlur('last')}
          placeholder='Last Name'
          valid={touched.last && !!account.last}
          invalid={touched.last && !account.last}
          value={account.last || ''} />
        <FormFeedback>Last Name is required</FormFeedback>
      </Col>

      <Col xs={12} sm={6}>
        <Label>Home Phone</Label>

        <PhoneNumber
          required
          onChange={onChange}
          onBlur={onBlur('admin_phone_home')}
          name='admin_phone_home'
          placeholder='Home Phone'
          invalid={touched.admin_phone_home && !account.admin_phone_home}
          value={account.admin_phone_home || ''} />

        <FormFeedback>Please enter a valid phone number</FormFeedback>
      </Col>


      <Col xs={12} sm={6}>
        <Label>Work Phone</Label>

        <PhoneNumber
          required
          onChange={onChange}
          onBlur={onBlur('admin_phone_work')}
          name='admin_phone_work'
          placeholder='Work Phone'
          invalid={touched.admin_phone_work && !account.admin_phone_work}
          value={account.admin_phone_work || ''} />

        <FormFeedback>Please enter a valid phone number</FormFeedback>
      </Col>

      <Col xs={12} sm={6}>
        <Label>Cell Phone</Label>

        <PhoneNumber
          required
          onChange={onChange}
          onBlur={onBlur('admin_phone_mobile')}
          placeholder='Cell Phone'
          name='admin_phone_mobile'
          invalid={touched.admin_phone_mobile && !account.admin_phone_mobile}
          value={account.admin_phone_mobile || ''} />

        <FormFeedback>Please enter a valid phone number</FormFeedback>
      </Col>
    </Row>
  );
}
