import React, { useState } from 'react';
import { Row, Col, Input, FormFeedback } from 'reactstrap';
import { PhoneNumber } from 'components/inputs';

export const AccountingRow = (props) => {
  const { title, disabled, required, onChange, prefix } = props;

  // Helpers
  const getName = key => `${prefix}${key}`;
  const getValue = key => props[`${prefix}${key}`] || '';

  const [touched, setTouched] = useState(false);
  const onBlur = () => setTouched(true);

  // Validation
  const phoneValue = getValue('number');
  const phoneRegex = /^(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}$/;
  const isPhoneInvalid = touched && phoneValue && !phoneRegex.test(phoneValue);

  const inputProps = { disabled, required, onChange };

  return (
    <Row id='accounting-row'>

      {title &&
        <Col xs={12}>
          <p className='title'>{title}</p>
        </Col>
      }

      <Col xs={6}>
        <label>Name of contact</label>
        <Input name={getName('name')} id={getName('name')} value={getValue('name')} {...inputProps} />
      </Col>

      <Col xs={6}>
        <label>Phone</label>
        <PhoneNumber name={getName('number')} id={getName('number')}
          value={phoneValue} {...inputProps}
          onBlur={onBlur}
          invalid={isPhoneInvalid}
        />
        <FormFeedback>Please enter a valid phone number</FormFeedback>
      </Col>

      <Col xs={6}>
        <label>Email</label>
        <Input type='email' name={getName('email')} id={getName('email')} value={getValue('email')} {...inputProps} />
      </Col>
    </Row>
  );
};
