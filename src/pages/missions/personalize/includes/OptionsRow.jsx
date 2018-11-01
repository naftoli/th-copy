import React from 'react';

import { Row, Col } from 'reactstrap';
import { Radio } from 'components/inputs';
import { isAdmin, isBC } from 'functions/login';
import { PlatoonSelect, SoldierSelect, ParshaSelect, BaseSelect } from 'components/selects';

const OptionsRow = props => {
  const { 
    login,    school_id,  class_id,
    user_id,  parsha_id,  lang,
    onSelectChange,       onLangChange
  } = props;

  return (
    <Row>
      <Col sm={6}>
        <label>Base</label>
        <BaseSelect
          required
          value={ school_id }
          isDisabled={ !isAdmin( login.code ) } 
          onChange={ onSelectChange('school_id') } />
      </Col>

      <Col sm={6}>
        <label>Platoon</label>
        <PlatoonSelect
          isClearable
          value={ class_id }
          schoolId={ school_id }
          openMenuOnFocus={ false }
          placeholder='All Platoons'
          isDisabled={ !isBC( login.code ) }
          onChange={ onSelectChange('class_id') } />
      </Col>

      <Col sm={6}>
        <label>Soldier</label>
        <SoldierSelect
          isClearable
          registeredOnly
          value={ user_id }
          classId={ class_id }
          schoolId={ school_id }
          openMenuOnFocus={ false } 
          placeholder='All Soldiers' 
          onChange={ onSelectChange('user_id') } />
      </Col>

      <Col sm={6}>
        <label>Parsha</label>
        <ParshaSelect
          isClearable
          value={ parsha_id }
          placeholder='Entire Year' 
          onChange={ onSelectChange('parsha_id') } />
      </Col>

      <Col sm={{ size: 6, offset: 3 }} className='lang-options'>
        <strong>Language</strong>
        <Radio
            value='1'
            name='lang'
            checked={ lang === '1' }
            onChange={ onLangChange }>
          English
        </Radio>
        <Radio
            value='2'
            name='lang'
            checked={ lang === '2' }
            onChange={ onLangChange }>
          Yiddish
        </Radio>
      </Col>
    </Row>
  )
}

export default OptionsRow;