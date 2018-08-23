import React, { Component } from 'react';
// components
import { Select, PhoneNumber } from 'components/inputs';
import { Row, Col, Input } from 'reactstrap';
// styles
// import './PlatoonPage.scss';

export class PlatoonRow extends Component {

  render() {
    const { inputProps, selectProps } = this.props;
    const { class_grade, class_sub, class_teacher, cell, email } = this.props.platoon;

    let grades = [
      'Pre-school 1', 'Pre-school 2', 'Pre-school 3', 'Pre1a', '1', 
      '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
    ];
    grades = grades.map( grade => ({label: grade, value: grade, id: 'class_grade'}) );
    const selectedGrade = { label: class_grade, value: class_grade };

    return (
      <Row>
        <Col xs={6}>
          <label>Grade</label>
          <Select options={ grades } value={ selectedGrade } { ...selectProps } />
        </Col>
        <Col xs={6}>
          <label>Sub (optional details, e.g. 'Boys' or 'א‬'.)</label>
          <Input name='class_sub' value={ class_sub } { ...inputProps } />
        </Col>
        <Col xs={12}>
          <label>Teacher</label>
          <Input name='class_teacher' value={ class_teacher } { ...inputProps } />
        </Col>
        <Col xs={6}>
          <label>Teacher Cell</label>
          <PhoneNumber name='cell' value={ cell } { ...inputProps } />
          <div className='invalid-message'>Please enter a valid phone number</div>
        </Col>
        <Col xs={6}>
          <label>Teacher E-Mail</label>
          <Input name='email' type='email' value={ email } { ...inputProps } />
          <div className='invalid-message'>Please enter a valid E-mail address</div>
        </Col>
      </Row>
    );
  }
}

export default PlatoonRow;
