import React from 'react';
import classnames from 'classnames';
// components
import { Row, Col, Button, ButtonGroup } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/selects';

const Step3 = ({ 
  school_id, class_id, selectChange, selection
}) => {
  // define classnames
  const className = classnames({
    'hide': selection.length === 0,
    'show': selection.length !== 0
  });
  // render page
  return (
    <div id='step-3' className={className}>
      <p className="title">Step 3: Select Transition for { selection.length } Soldiers</p>
      <Row>
        <Col xs={6}>
          <label>To Base</label>
          <BaseSelect value={ school_id } fetchAll 
            onChange={ selectChange('school_id') } />
        </Col>
        <Col xs={6}>
          <label>To Platoon</label>
          <PlatoonSelect value={ class_id } school_id={ school_id } 
            onChange={ selectChange('class_id') } />
        </Col>
        <Col xs={12}>
          <label>Transition Action</label>
          <ButtonGroup>
            <Button color='primary'>Move Soldiers</Button>
            <Button color='danger'>Graduate Soldiers ( Remove From Base )</Button>
          </ButtonGroup>
        </Col>
      </Row>
    </div>
  );
};

export default Step3;
