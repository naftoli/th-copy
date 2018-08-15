import React from 'react';
// components
import { Row, Col } from 'reactstrap';

const RegistrationRow = ({ soldier }) => {
  const { start_date, registered_at } = soldier;
  return (
    <Row id='registration-row'>
      <Col xs='12'>
        <p className='title'>Registration Information</p>
        <label>Member Since:</label>
        <h4>{ start_date || 'N/A' }</h4>
        <label>Registered:</label>
        <h4>{ registered_at || 'Not Registered.'}</h4>
      </Col>
    </Row>
  );
}

export default RegistrationRow;
