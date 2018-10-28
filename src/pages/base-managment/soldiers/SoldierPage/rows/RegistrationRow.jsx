import React from 'react';
// components
import { Row, Col } from 'reactstrap';

const RegistrationRow = ({ soldier }) => {
  const { start_date, registered_at } = soldier;
  return (
    <Row id='registration-row'>
      <Col xs={12}>
        <p className='title'>Registration Information</p>
      </Col>

      <Col xs={12} sm={6}>
        <label>Member Since:</label>
        <h4>{ start_date || 'N/A' }</h4>
      </Col>

      <Col xs={12} sm={6}>
        <label>Registered:</label>
        <h4>{ registered_at || 'Not Registered.'}</h4>
      </Col>
    </Row>
  );
}

export default RegistrationRow;
