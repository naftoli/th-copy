import React from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/selects';
import { InlineSync } from 'components/ui/loading';

const Step1 = ({ school_id, class_id, selectChange, onSubmit, loading }) => (
  <div id='step-1'>
    <p className="title">Step 1: Select Platoon</p>
    <Row>
      <Col sm={5}>
        <label>From Base</label>
        <BaseSelect value={ school_id } fetchAll 
          onChange={ selectChange( 'school_id' ) } />
      </Col>
      <Col sm={4}>
        <label>From Platoon</label>
        <PlatoonSelect value={ class_id } schoolId={ school_id }
          onChange={ selectChange( 'class_id' ) } showNoneOption />
      </Col>
      <Col sm={3}>
        <Button color='primary' onClick={ onSubmit }>
          <InlineSync loading={loading} /> Load Soldiers
        </Button>
      </Col>
    </Row>
  </div>
);

export default Step1;
