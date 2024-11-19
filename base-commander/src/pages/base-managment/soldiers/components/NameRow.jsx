import React, { Fragment } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';

export const NameRow = ( { inst, soldier, onChange } ) => {
  const { first, last, first_he, last_he } = soldier;
  // change the hebrew text
  const inputProps = { onChange, maxLength: 128 };

  return (
    <Row>
      <Col sm='6'>
        <label htmlFor='first'>First Name</label>
        <Input id='first' required value={ first } { ...inputProps }
          pattern="^[a-zA-Z\s\-'&quot;]{3,60}$" title="3-60 English letters"/>
        <div className='invalid-message'>3-60 <em>English</em> letters</div>
      </Col>
      <Col sm='6'>
        <label htmlFor='last'>Last Name</label>
        <Input id='last' required value={ last } { ...inputProps }
          pattern="^[a-zA-Z\s\-'&quot;]{3,60}$" title="3-60 English letters"/>
        <div className='invalid-message'>3-60 <em>English</em> letters</div>
      </Col>
      { inst !== 10 &&
        <Fragment>
          <Col sm='6' dir='rtl'>
            <label htmlFor='first_he'>שם פרטי (First Name)</label>
            <Input id='first_he' value={ first_he } { ...inputProps }
              pattern="^[\u0590-\u05FF\s\-'&quot;]{2,60}$" title="2-60 Hebrew letters"/>
            <div className='invalid-message'>3-60 <em>Hebrew</em> letters</div>
          </Col>
          <Col sm='6' dir='rtl'>
            <label htmlFor='last_he'>שם משפחה (Last Name)</label>
            <Input id='last_he' value={ last_he } { ...inputProps }
              pattern="^[\u0590-\u05FF\s\-'&quot;]{2,60}$" title="2-60 Hebrew letters"/>
            <div className='invalid-message'>3-60 <em>Hebrew</em> letters</div>
          </Col>
        </Fragment>
      } 
    </Row>
  );
}
