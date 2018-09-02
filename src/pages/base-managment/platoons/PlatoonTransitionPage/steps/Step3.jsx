import React from 'react';
// components
import { Row, Col, Button, ButtonGroup } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/inputs';
import { FontAwesome } from 'components/ui';

const Step3 = ({ 
  school_id, class_id, selectChange, 
  selection, move, discharge
}) => {
  // render page
  return (
    <div id='step-3'>
      <p className="title">Step 3: Select Transition for { selection.length } Soldiers</p>
      <Row>
        <Col sm={6} xl={4}>
          <label>To Base</label>
          <BaseSelect value={ school_id } fetchAll 
            onChange={ selectChange('school_id') } />
        </Col>
        <Col sm={6} xl={4}>
          <label>To Platoon</label>
          <PlatoonSelect value={ class_id } schoolId={ school_id } 
            onChange={ selectChange('class_id') } />
        </Col>
        <Col sm={12} xl={4}>
          <ButtonGroup>
            <Button color='primary' onClick={ move }>
              <FontAwesome icon="exchange-alt" />{' '}
              Transition (Move) Soldiers
            </Button>
            <Button color='danger' onClick={ discharge }>
              <FontAwesome icon="trash-alt" />{' '}
              Discharge (Remove) Soldiers 
            </Button>
          </ButtonGroup>
        </Col>
      </Row>
    </div>
  );
};

export default Step3;
