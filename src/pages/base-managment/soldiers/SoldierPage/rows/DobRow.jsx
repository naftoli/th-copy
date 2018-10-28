import React from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import { Date } from 'components/inputs';
// functions
import moment from 'moment';

const DobRow = ( { soldier, onChange, showHe, children } ) => {
  const { dob, dob_he } = soldier;
  return (
    <Row>
      <Col xs='6'>
        <label>Date of Birth</label>
        <Date
          value={ dob ? moment( dob ) : undefined } 
          onChange={ onChange } required
          // client side date validations
          minDate={ moment().subtract( 20, 'years' ) } 
          maxDate={ moment().subtract( 5, 'years' ) } />
      </Col>
      { showHe && 
        <Col xs='6' dir='rtl'>
          <label>יום הולדת</label>
          <Input disabled value={ dob_he }/>
        </Col>
      }
      { children }
    </Row>
  )
}

export default DobRow;
