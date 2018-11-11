import React from 'react';
// components
import { Row, Col, Input } from 'reactstrap';

export const NameRow = ( { soldier, onChange } ) => {
  const { first, last, first_he, last_he } = soldier;
  // change the hebrew text
  const inputProps = { onChange, maxLength: 128 };

  return (
    <Row>
      <Col sm='6'>
        <label htmlFor='first'>First Name</label>
        <Input id='first' required value={ first } { ...inputProps }
          pattern='^[\D\s]{3,128}$' title="3-128 letters"/>
        <div className='invalid-message'>3-128 letters</div>
      </Col>
      <Col sm='6'>
        <label htmlFor='last'>Last Name</label>
        <Input id='last' required value={ last } { ...inputProps }
          pattern='^[\D\s]{3,128}$' title="3-128 letters"/>
        <div className='invalid-message'>3-128 letters</div>
      </Col>
      <Col sm='6' dir='rtl'>
        <label htmlFor='first_he'>שם פרטי (First Name)</label>
        <Input id='first_he' value={ first_he } { ...inputProps }
          pattern='^[^a-zA-Z]{2,128}$' title="2-128 Hebrew letters"/>
        <div className='invalid-message'>2-128 <em>Hebrew</em> letters</div>
      </Col>
      <Col sm='6' dir='rtl'>
        <label htmlFor='last_he'>שם משפחה (Last Name)</label>
        <Input id='last_he' value={ last_he } { ...inputProps }
          pattern='^[^a-zA-Z]{2,128}$' title="2-128 Hebrew letters"/>
        <div className='invalid-message'>2-128 <em>Hebrew</em> letters</div>
      </Col>
    </Row>
  );
}
