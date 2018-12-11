import React from 'react';
// components
import { Row, Col } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/selects';
import { isAdmin, isBC } from 'functions/login';

export const BasePlatoonRow = props => {
  const {
    code, schoolId, classId, 
    onChange, required, isClearable 
  } = props;
  // change the hebrew text
  const inputProps = { isClearable, required };

  return (
    <Row>
      <Col sm={ 6 }>
        <label>Base</label>
        <BaseSelect
          { ...inputProps }
          value={ schoolId }
          isDisabled={ !isAdmin( code ) }
          onChange={ onChange('school_id') } />
      </Col>

      <Col sm={ 6 }>
        <label>Platoon</label>
        <PlatoonSelect
          { ...inputProps }
          value={ classId }
          schoolId={ schoolId }
          isDisabled={ !isBC( code ) }
          onChange={ onChange('class_id') } />
      </Col>
    </Row>
  );
}
