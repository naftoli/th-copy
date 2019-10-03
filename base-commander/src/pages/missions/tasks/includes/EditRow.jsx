import React, { Fragment } from 'react';

import { Row, Col, Input } from 'reactstrap';
import { Label, Checkbox } from 'components/inputs';
import { LabelSelect } from 'components/selects';

const EditRow = props => {
  const {
    label_id,       name,
    short_name,     grid_marking,
    onInputChange,  mission_marking,
    onSelectChange, onCheckboxChange,
  } = props;

  return (
    <Fragment>
      <Row>
        <Col sm={ 6 }>
          <Label htmlFor='short_name'>
            Title
          </Label>
          <Input
            required
            id='short_name'
            name='short_name'
            value={ short_name }
            onChange={ onInputChange }/>
        </Col>

        <Col sm={ 6 }>
          <Label>Label - Frequency</Label>
          <LabelSelect
            required
            value={ label_id }
            onChange={ onSelectChange( 'label_id' ) }/>
        </Col>

        <Col sm={ 12 }>
          <Label htmlFor='name'>
            Details
          </Label>
          <Input
            required
            id='name'
            name='name'
            value={ name }
            onChange={ onInputChange }/>
        </Col>

        <Col sm={ 12 }>
          <Label>Show Task On:</Label>
          
          <Checkbox
              name='mission_marking'
              checked={ mission_marking }
              onChange={ onCheckboxChange }>
            Mission Sheets
          </Checkbox>

          <Checkbox
              name='grid_marking'
              checked={ grid_marking }
              onChange={ onCheckboxChange }>
            Teacher's Marking Grid
          </Checkbox>
        </Col>

      </Row>
    </Fragment>
  );
}

export default EditRow;
