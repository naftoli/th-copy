import React, { Component } from 'react';
// components
import { Select, PhoneNumber } from 'components/inputs';
import { Row, Col, Input } from 'reactstrap';
import { findOption } from 'functions/selects'

export class PlatoonRow extends Component {

  render() {
    const { inputProps, selectProps } = this.props;
    const { 
      class_grade, class_sub, class_teacher, cell, email,
      miles_per_soldier, miles_balance
    } = this.props.platoon;

    let grades = [
      'Pre-school 1', 'Pre-school 2', 'Pre-school 3', 'Pre1a', '1', 
      '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
    ];
    grades = grades.map( grade => ({ label: grade, value: grade, id: 'class_grade' }) );
    const selectedGrade = findOption( grades, class_grade );

    return (
      <Row>
        <Col xs={6}>
          <label>Grade</label>
          <Select options={ grades } id='class_grade' value={ selectedGrade } { ...selectProps } />
        </Col>
        <Col xs={6}>
          <label>Sub (optional details, e.g. 'Boys' or 'א‬'.)</label>
          <Input name='class_sub' value={ class_sub || '' } { ...inputProps } required={ false }/>
        </Col>
        <Col xs={12}>
          <label>Teacher Name</label>
          <Input name='class_teacher' value={ class_teacher || '' } { ...inputProps } />
        </Col>
        <Col xs={6}>
          <label>Teacher Phone</label>
          <PhoneNumber name='cell' value={ cell || '' } { ...inputProps } />
          <div className='invalid-message'>Please enter a valid phone number</div>
        </Col>
        <Col xs={6}>
          <label>Teacher E-Mail</label>
          <Input name='email' type='email' value={ email || '' } { ...inputProps } />
          <div className='invalid-message'>Please enter a valid E-mail address</div>
        </Col>
        <Col xs={6}>
          <label>Miles per Soldier (per month)</label>
          <Input 
            type='number' name='miles_per_soldier' 
            value={ miles_per_soldier } { ...inputProps } />
        </Col>
        <Col xs={6}>
          <label>Current Miles Balance</label>
          <Input 
            type='number' name='miles_balance' 
            value={ miles_balance } { ...inputProps } />
        </Col>
      </Row>
    );
  }
}

export default PlatoonRow;
