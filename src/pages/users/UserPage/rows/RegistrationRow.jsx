import React from 'react';
// components
import { Row, Col } from 'reactstrap';
// functions
import moment from 'moment';
import julian from 'julian';

const RegistrationRow = ( soldier ) => {
  const { user_start_date, user_registered } = soldier;
  return (
    <Row id='registration-row'>
      <Col xs='12'>
        <p className='title'>Registration Information</p>
        <label>Member Since:</label>
        <h4>{
          user_start_date ? 
          moment( julian.toDate( user_start_date ) ).format("LLLL") :
          'N/A'
        }</h4>
        <label>Registered:</label>
        <h4>{ user_registered ? moment( user_registered ).format("LLLL") : 'Not Registered.'}</h4>
      </Col>
    </Row>
  );
}

export default RegistrationRow;
