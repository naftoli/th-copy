import React, { Fragment } from 'react';

import { Row, Col, Input } from 'reactstrap';
import { julianToday } from 'functions/dates';
import { Radio, Label, Checkbox } from 'components/inputs';
import {
  LabelSelect, ParshaSelect,  BaseSelect,
  GradeSelect, SubjectSelect, MissionTypeSelect
} from 'components/selects';
import { isAdmin } from 'functions/login';

const OptionsRow = props => {
  const {
    login,
    // data values
    parsha_ids,     lang,     label_id,
    subject_id,     task,     short_name,
    school_type_id, grades,   school_id,
    grid_marking,   mission_marking,
    onInputChange,  onCheckboxChange,
    onSelectChange, onMultiSelectChange,
  } = props;

  // hide 1, 40, and 94 from the dropdown
  const filterSubjects = ({ subject_id }) =>
    ![1, 40, 94].includes( subject_id );

  return (
    <Fragment>
      <Row>
        { isAdmin( login.code ) &&
          <Col xs={12}>
            <Label>Base</Label>
            <BaseSelect
              required
              value={ school_id }
              onChange={ onSelectChange( 'school_id' ) } />
          </Col>
        }

        <Col sm={ 6 }>
          <Label>Campaign</Label>
          <SubjectSelect
            required
            value={ subject_id }
            filter={ filterSubjects }
            onChange={ onSelectChange( 'subject_id' ) } />
        </Col>

        <Col sm={ 6 }>
          <Label htmlFor='short_name'>
            Title (e.g. Modeh Ani)
          </Label>
          <Input
            required
            id='short_name'
            name='short_name'
            value={ short_name }
            onChange={ onInputChange }/>
        </Col>

        <Col sm={ 12 }>
          <Label htmlFor='task'>
            Details (e.g. I did my quota of volunteer hours)
          </Label>
          <Input
            required
            id='task'
            name='task'
            value={ task }
            onChange={ onInputChange }/>
        </Col>

        <Col sm={ 6 }>
          <Label>Language</Label>
          <Radio value='1'  name='lang' required
            checked={ lang === '1' } onChange={ onInputChange }>
            English
          </Radio>
          <Radio value='2'  name='lang' required
            checked={ lang === '2' } onChange={ onInputChange }>
            Yiddish
          </Radio>
        </Col>

        <Col sm={ 6 }>
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

        <Col sm={ 6 }>
          <Label>Label - Frequency</Label>
          <LabelSelect
            required
            value={ label_id }
            onChange={ onSelectChange( 'label_id' ) }/>
        </Col>

        <Col sm={ 6 }>
          <Label>Mission Type</Label>
          <MissionTypeSelect
            required
            value={ school_type_id }
            onChange={ onSelectChange( 'school_type_id' ) }/>
        </Col>

        <Col xs={ 12 }>
          <Label>Grade(s)</Label>
          <GradeSelect
            isMulti
            isClearable
            values={ grades }
            openMenuOnFocus={ false }
            placeholder='All Grades'
            onChange={ onMultiSelectChange('grades') } />
        </Col>

        <Col xs={ 12 }>
          <Label>Parsha(s)</Label>
          <ParshaSelect
            isMulti
            isClearable
            values={ parsha_ids }
            openMenuOnFocus={ false }
            startDate={ julianToday() }
            placeholder='All Remaining Parshos'
            onChange={ onMultiSelectChange('parsha_ids') } />
        </Col>
      </Row>
    </Fragment>
  );
}

export default OptionsRow;
