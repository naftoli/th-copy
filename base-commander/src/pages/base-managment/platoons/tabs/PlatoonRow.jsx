import React, { Component } from 'react';
// components
import { Select } from 'components/inputs';
import { Row, Col, Input, Button } from 'reactstrap';
import { findOption } from 'functions/selects';
import { FontAwesome, NumberDisplay } from 'components/ui';

export class PlatoonRow extends Component {

  render() {
    const { inputProps, onSelectChange, onDelete } = this.props;
    const { 
      class_grade, class_sub, class_teacher, cell, email,
      soldiers, miles_per_soldier, miles_balance,
    } = this.props.platoon;

    let grades = [
      'Pre-school 1', 'Pre-school 2', 'Pre-school 3', 'Pre1a', '1', 
      '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
    ];
    grades = grades.map( grade => ({ label: grade, value: grade }) );
    const selectedGrade = findOption( grades, class_grade );

    return (
      <Row>
        <Col sm={6}>
          <label>Grade</label>
          <Select
            required
            options={ grades }
            value={ selectedGrade }
            onChange={ onSelectChange('class_grade') } />
        </Col>

        <Col sm={6}>
          <label>Sub (optional details, e.g. 'Boys' or 'א‬'.)</label>
          <Input name='class_sub' value={ class_sub || '' } { ...inputProps } required={ false }/>
        </Col>

        <Col sm={12}>
          <label>Teacher Name</label>
          <Input required name='class_teacher' value={ class_teacher || '' } { ...inputProps } />
        </Col>

        <Col sm={6}>
          <label>Teacher Phone</label>

          <Input required name='cell' type='tel' value={ cell || '' }
            { ...inputProps } title='1 or more valid phone numbers (, or ; seperated)' // one or more valid phone numbers
            pattern='^(((\+[0-9]{1,3}[0-9 ]{9,})|((?:1 |\()?[0-9]{3}(?: |\) |-)?[0-9]{3}(?: |-)?[0-9]{4}))[,;])*((\+[0-9]{1,3}[0-9 ]{9,})|((?:1 |\()?[0-9]{3}(?: |\) |-)?[0-9]{3}(?: |-)?[0-9]{4}))$'
            />
          <div className='invalid-message'>1 or more valid phone numbers (, or ; seperated)</div>
        </Col>

        <Col sm={6}>
          <label>Teacher E-Mail</label>

          <Input required name='email' value={ email || '' } { ...inputProps }
            title='1 or more valid E-mail addresses (, or ; seperated)'
            // one or more emails with a , or ; at the end
            pattern='^(\s?[^\s,]+@[^\s,]+\.[^\s,]+\s?[,;])*(\s?[^\s,]+@[^\s,]+\.[^\s,]+)$'
            />
          <div className='invalid-message'>1 or more valid E-mail addresses (, or ; seperated)</div>
        </Col>

        { soldiers && soldiers.length === 0 && 
          <Col sm={12}>
            <Button color='danger' onClick={ onDelete } id='delete'>
              <FontAwesome icon='trash' /> Delete Platoon
            </Button>
          </Col>
        }

        <Col xs={12}>
          <p className='title'>
            Achivement Card Settings
          </p>
        </Col>

        <Col sm={6}>
          <label>Miles per Soldier (per month)</label>
          <Input 
            type='number' name='miles_per_soldier' 
            value={ miles_per_soldier } { ...inputProps } />
        </Col>

        <Col sm={6}>
          <label>Current Miles Balance</label>
          <Input 
            type='number' name='miles_balance'  min='0' max='99999999999'
            value={ miles_balance } { ...inputProps } />
          <div className='invalid-message'>0 to <NumberDisplay value={ 99999999999 } /></div>
        </Col>

      </Row>
    );
  }
}
