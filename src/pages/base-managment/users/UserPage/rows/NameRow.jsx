import React from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
// functions
import { toHebrew } from 'functions/utils';

const RegistrationRow = ( { soldier, onChange, required } ) => {
  const { first, last, first_he, last_he } = soldier;
  // change the hebrew text
  const hebrewChange = ( event ) => {
    event.target.value = toHebrew( event.target.value );
    onChange( event );
  }

  return (
    <Row>
      <Col xs='6'>
        <label>First Name</label>
        <Input id='first' value={ first } onChange={ onChange } required={ required } 
          pattern='^[a-zA-Z\s]{3,}$' title="Three or more letters"/>
        <div className='invalid-message'>Please enter 3 or more letters</div>
      </Col>
      <Col xs='6'>
        <label>Last Name</label>
        <Input id='last' value={ last } onChange={ onChange } required={ required }
          pattern='^[a-zA-Z\s]{3,}$' title="Three or more letters"/>
        <div className='invalid-message'>Please enter 3 or more letters</div>
      </Col>
      <Col xs='6' dir='rtl'>
        <label>שם פרטי (First Name)</label>
        <Input id='first_he' value={ first_he } onChange={ hebrewChange } required={ required }
          pattern='^[א-ת\s]{3,}$' title="Three or more Hebrew letters"/>
        <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
      </Col>
      <Col xs='6' dir='rtl'>
        <label>שם משפחה (Last Name)</label>
        <Input id='last_he' value={ last_he } onChange={ hebrewChange } required={ required }
          pattern='^[א-ת\s]{3,}$' title="Three or more Hebrew letters"/>
        <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
      </Col>
    </Row>
  );
}

export default RegistrationRow;
