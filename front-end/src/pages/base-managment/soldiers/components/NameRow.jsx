import React, { Fragment, useState } from 'react';
// components
import { Row, Col, Input, FormFeedback } from 'reactstrap';

export const NameRow = ({ inst, soldier, onChange }) => {
  const { first, last, first_he, last_he } = soldier;

  // Track touched state for validation
  const [touched, setTouched] = useState({});
  const onBlur = (field) => () => {
    setTouched(prev => ({ ...prev, [field]: true }));
  };

  // Regex patterns
  const namePattern = /^[a-zA-Z\s\-'"]{3,60}$/;
  const hePattern = /^[\u0590-\u05FF\s\-'"]{2,60}$/;

  // Validation helpers
  const isValid = (value, pattern) => pattern.test(value);
  const isInvalid = (field, value, pattern) => {
    // Invalid if touched AND (empty/required OR pattern mismatch)
    // Note: If required, empty is invalid.
    return touched[field] && !isValid(value, pattern);
  };

  // change the hebrew text
  const inputProps = { onChange, maxLength: 128 };

  return (
    <Row>
      <Col sm='6'>
        <label htmlFor='first'>First Name</label>
        <Input
          id='first'
          required
          value={first || ''}
          {...inputProps}
          onBlur={onBlur('first')}
          valid={touched.first && isValid(first, namePattern)}
          invalid={isInvalid('first', first, namePattern)}
        />
        <FormFeedback>3-60 <em>English</em> letters</FormFeedback>
      </Col>
      <Col sm='6'>
        <label htmlFor='last'>Last Name</label>
        <Input
          id='last'
          required
          value={last || ''}
          {...inputProps}
          onBlur={onBlur('last')}
          valid={touched.last && isValid(last, namePattern)}
          invalid={isInvalid('last', last, namePattern)}
        />
        <FormFeedback>3-60 <em>English</em> letters</FormFeedback>
      </Col>
      {inst !== 10 &&
        <Fragment>
          <Col sm='6' dir='rtl'>
            <label htmlFor='first_he'>שם פרטי (First Name)</label>
            <Input
              id='first_he'
              value={first_he || ''}
              {...inputProps}
              onBlur={onBlur('first_he')}
              valid={touched.first_he && isValid(first_he, hePattern)}
              invalid={isInvalid('first_he', first_he, hePattern)}
            />
            <FormFeedback>2-60 <em>Hebrew</em> letters</FormFeedback>
          </Col>
          <Col sm='6' dir='rtl'>
            <label htmlFor='last_he'>שם משפחה (Last Name)</label>
            <Input
              id='last_he'
              value={last_he || ''}
              {...inputProps}
              onBlur={onBlur('last_he')}
              valid={touched.last_he && isValid(last_he, hePattern)}
              invalid={isInvalid('last_he', last_he, hePattern)}
            />
            <FormFeedback>2-60 <em>Hebrew</em> letters</FormFeedback>
          </Col>
        </Fragment>
      }
    </Row>
  );
}
