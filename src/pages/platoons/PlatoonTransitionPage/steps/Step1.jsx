import React from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/selects';

const Step1 = ({ school_id, class_id, selectChange }) => (
  <div id='step-1'>
    <p className="title">Step 1: Select Platoon</p>
    <Row>
      <Col xs={5}>
        <label>From Base</label>
        <BaseSelect value={ school_id } fetchAll 
          onChange={ selectChange( 'school_id' ) } />
      </Col>
      <Col xs={4}>
        <label>From Platoon</label>
        <PlatoonSelect value={ class_id } school_id={ school_id } fetchAll 
          onChange={ selectChange( 'class_id' ) } isClearable />
      </Col>
      <Col xs={3}>
        <Button color='primary'>Load Soldiers</Button>
      </Col>
    </Row>
  </div>
);

export default Step1;
