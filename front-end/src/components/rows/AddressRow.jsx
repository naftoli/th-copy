import React, { useState, Fragment } from 'react';
import { Row, Col, Input, FormFeedback } from 'reactstrap';
import { PhoneNumber } from 'components/inputs';

export const AddressRow = (props) => {
  const { title = 'Address', prefix = '', disabled, required, onChange, showPhone, shipping_phone, hideShipping } = props;

  const getName = (key) => `${prefix}${key}`;
  const getValue = (key) => props[`${prefix}${key}`] || '';

  const [touched, setTouched] = useState({});
  const onBlur = (key) => () => setTouched(prev => ({ ...prev, [key]: true }));

  // Validation Patterns
  const patterns = {
    city: /^.{3,}$/,
    state: /^[A-Za-z\s]{2,255}$/,
    zip: /^.{3,255}$/,
    country: /^.{2,255}$/
  };

  const isValid = (value, pattern) => pattern.test(value);
  const isInvalid = (field, value, pattern) => {
    return touched[field] && !isValid(value, pattern);
  };

  // Specific checks
  const isInvalidRequired = (field, value) => touched[field] && !value;

  const inputProps = { disabled, required, onChange };

  return (
    <Row id='address-row'>

      {!hideShipping && title &&
        <Col xs={12}>
          <p className='title'>{title}</p>
        </Col>
      }

      {!hideShipping &&
        <Fragment>
          <Col xs={12} xl={6}>
            <label>Address 1</label>
            <Input name={getName('address1')} id={getName('address1')} placeholder='792 Eastern Parkway'
              value={getValue('address1')} {...inputProps} maxLength={255}
              onBlur={onBlur('address1')}
              // Address 1 usually required? Previous code didn't show error text but Input likely required.
              // Assuming required if 'required' prop passed to row.
              invalid={required && isInvalidRequired('address1', getValue('address1'))}
            />
            <FormFeedback>Please enter a valid address</FormFeedback>
          </Col>

          <Col xs={12} xl={6}>
            <label>Address 2</label>
            <Input name={getName('address2')} id={getName('address2')}
              placeholder='5th Floor' value={getValue('address2')}
              {...inputProps} required={false} maxLength={255} />
          </Col>

          <Col xs={6} xl={showPhone ? 6 : 4}>
            <label>City</label>
            <Input name={getName('city')} id={getName('city')}
              value={getValue('city')} {...inputProps} placeholder='Brooklyn'
              onBlur={onBlur('city')}
              maxLength={255}
              invalid={isInvalid('city', getValue('city'), patterns.city)}
            />
            <FormFeedback>Please enter 3 or more letters</FormFeedback>
          </Col>

          <Col xs={3} xl={showPhone ? 3 : 2}>
            <label>State</label>
            <Input name={getName('state')} id={getName('state')}
              value={getValue('state')} {...inputProps} placeholder='NY'
              onBlur={onBlur('state')}
              maxLength={255}
              invalid={isInvalid('state', getValue('state'), patterns.state)}
            />
            <FormFeedback>Please enter a valid state</FormFeedback>
          </Col>

          <Col xs={3} xl={showPhone ? 3 : 2}>
            <label>Zip</label>
            <Input name={getName('postal')} id={getName('postal')}
              value={getValue('postal')} {...inputProps} placeholder='11213'
              onBlur={onBlur('postal')}
              maxLength={255}
              invalid={isInvalid('postal', getValue('postal'), patterns.zip)}
            />
            <FormFeedback>Please enter 3 to 255 letters</FormFeedback>
          </Col>

          <Col xs={showPhone ? 6 : 12} xl={showPhone ? 6 : 4}>
            <label>Country</label>
            <Input name={getName('country')} id={getName('country')}
              value={getValue('country')} {...inputProps} placeholder='USA'
              onBlur={onBlur('country')}
              maxLength={255}
              invalid={isInvalid('country', getValue('country'), patterns.country)}
            />
            <FormFeedback>Please enter 3 to 255 letters</FormFeedback>
          </Col>
        </Fragment>
      }

      {showPhone &&
        <Col xs={6}>
          <label>Phone</label>
          <PhoneNumber name={getName('phone')} id={getName('phone')}
            value={shipping_phone} {...inputProps} placeholder='(718) 467-6630'
            onBlur={onBlur('phone')}
            invalid={touched.phone && !shipping_phone} // Assuming required
          />
          <FormFeedback>Please enter a valid phone number</FormFeedback>
        </Col>
      }
    </Row>
  );
}

