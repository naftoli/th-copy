import React from 'react';
// components
import { Row, Col, Input } from 'reactstrap';

const NameRow = ( { soldier, onChange, required } ) => {
  const { first, last, first_he, last_he } = soldier;
  // change the hebrew text
  const inputProps = { onChange, required };

  return (
    <Row>
      <Col xs='6'>
        <label>First Name</label>
        <Input id='first' value={ first } { ...inputProps }
          pattern='^[a-zA-Z-\s]{3,}$' title="Three or more letters"/>
        <div className='invalid-message'>Please enter 3 or more letters</div>
      </Col>
      <Col xs='6'>
        <label>Last Name</label>
        <Input id='last' value={ last } { ...inputProps }
          pattern='^[a-zA-Z-\s]{3,}$' title="Three or more letters"/>
        <div className='invalid-message'>Please enter 3 or more letters</div>
      </Col>
      <Col xs='6' dir='rtl'>
        <label>שם פרטי (First Name)</label>
        <Input id='first_he' value={ first_he } { ...inputProps }
          pattern='^[^a-zA-Z]{3,}$' title="Three or more Hebrew letters"/>
        <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
      </Col>
      <Col xs='6' dir='rtl'>
        <label>שם משפחה (Last Name)</label>
        <Input id='last_he' value={ last_he } { ...inputProps }
          pattern='^[^a-zA-Z]{3,}$' title="Three or more Hebrew letters"/>
        <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
      </Col>
    </Row>
  );
}

export default NameRow;
