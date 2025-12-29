import React from 'react';
import { PhoneNumber, Password } from 'components/inputs';
import { Row, Col, Input } from 'reactstrap';

const EditStaffRow = (props) => {
  const {
    username, password, title, first, last, email, work, cell,
    onChange, disabled, required
  } = props;
  const inputProps = { onChange, disabled, required };

  const [touched, setTouched] = React.useState({});
  const onBlur = (field) => () => setTouched(prev => ({ ...prev, [field]: true }));

  // Validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRegex = /^(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}$/;

  const isEmailInvalid = touched.email && email && !emailRegex.test(email);
  const isWorkInvalid = touched.work && work && !phoneRegex.test(work);
  const isCellInvalid = touched.cell && cell && !phoneRegex.test(cell);

  return (
    <Row>
      <Col xs={6}>
        <label>Username</label>
        <Input name='username' value={username} {...inputProps} />
      </Col>
      <Col xs={6}>
        <label>Password</label>
        <Password value={password} {...inputProps} tabToggle />
      </Col>
      <Col xs={4} sm={3}>
        <label>Title</label>
        <Input name='title' value={title} {...inputProps} required={false} />
      </Col>
      <Col xs={8} sm={4}>
        <label>First Name</label>
        <Input name='first' value={first} {...inputProps} />
      </Col>
      <Col xs={12} sm={5}>
        <label>Last Name</label>
        <Input name='last' value={last} {...inputProps} />
      </Col>
      <Col xs={12}>
        <label>E-Mail</label>
        <Input name='email' type='email' value={email} {...inputProps} required={false}
          onBlur={onBlur('email')}
          invalid={isEmailInvalid}
        />
        <div className="invalid-feedback">Please enter a valid E-mail address</div>
      </Col>
      <Col xs={12} sm={6}>
        <label>Work Phone</label>
        <PhoneNumber name='work' value={work} {...inputProps}
          onBlur={onBlur('work')}
          invalid={isWorkInvalid}
        />
        <div className="invalid-feedback">Please enter a valid phone number</div>
      </Col>
      <Col xs={12} sm={6}>
        <label>Cell Phone</label>
        <PhoneNumber name='cell' value={cell} {...inputProps}
          onBlur={onBlur('cell')}
          invalid={isCellInvalid}
        />
        <div className="invalid-feedback">Please enter a valid phone number</div>
      </Col>
    </Row>
  );
}

export default EditStaffRow;
