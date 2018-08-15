import React from 'react';
import { PhoneNumber, Password } from 'components/inputs';
import { Row, Col, Input } from 'reactstrap';

const EditStaffRow = ( props ) => {
  const inputProps = { onChange: props.onChange };
  const { username, password, title, first, last, email, work, cell } = props;
  return (
    <Row>
      <Col xs={6}>
        <label>Username</label>
        <Input name='username' value={ username } {...inputProps} />
      </Col>
      <Col xs={6}>
        <label>Password</label>
        <Password value={ password } {...inputProps} tabToggle />
      </Col>
      <Col xs={4}>
        <label>Title</label>
        <Input name='title' value={ title } {...inputProps} />
      </Col>
      <Col xs={8} sm={4}>
        <label>First Name</label>
        <Input name='first' value={ first } {...inputProps} />
      </Col>
      <Col xs={12} sm={4}>
        <label>Last Name</label>
        <Input name='last' value={ last } {...inputProps} />
      </Col>
      <Col xs={12}>
        <label>E-Mail</label>
        <Input name='email' value={ email } {...inputProps} />
      </Col>
      <Col xs={12} sm={6}>
        <label>Work Phone</label>
        <PhoneNumber name='work' value={ work } {...inputProps} />
        <div className='invalid-message'>Please enter a valid phone number</div>
      </Col>
      <Col xs={12} sm={6}>
        <label>Cell Phone</label>
        <PhoneNumber name='cell' value={ cell } {...inputProps} />
        <div className='invalid-message'>Please enter a valid phone number</div>
      </Col>
    </Row>
  );
}

export default EditStaffRow;
