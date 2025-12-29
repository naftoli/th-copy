import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
// components
import { Link } from 'react-router-dom';
import { FontAwesome, Callout } from 'components/ui';
import { Row, Col, Button, Card, Input, InputGroup } from 'reactstrap';
// functions
import is from 'is_js';
// styles
import './StaffRow.scss';

export const StaffRow = ({
  admin_id,
  first,
  last,
  username,
  email,
  disconnect = () => { }
}) => {

  const handleDisconnect = () => {
    disconnect(admin_id)
  }

  return (
    <Card className='StaffRow'>
      <Row>

        <Col xs='12'>
          <p className='staff-name'>
            Name: <Link to={`/bm/staff/${admin_id}`}>{last}, {first}</Link>
          </p>
        </Col>

        <Col xs={7} xl={3}>
          <label>Username</label>
          <Input disabled value={username || ''} />
        </Col>

        <Col xs={{ size: 12, order: 2 }} xl={{ size: 6, order: 1 }}>
          <label>E-Mail</label>
          <Input disabled value={email || ''} />
        </Col>

        <Col xs={{ size: 5, order: 1 }} xl={{ size: 3, order: 2 }}>
          <label>Actions</label>
          <Button color='danger' onClick={handleDisconnect}>
            Disconnect
          </Button>
        </Col>

      </Row>
    </Card>
  );
}

StaffRow.defaultProps = {
  first: '', last: '', username: '', email: '',
  disconnect: () => { }, admin_id: 0
}

export const NewStaffRow = ({ onSubmit }) => {

  const emailRef = React.useRef();

  const onClick = e => {
    const emailInput = emailRef.current;
    // if nothing was entered, focus on the input
    if (!emailInput.value) return emailInput.focus();
    // valid emails we look for the email address
    if (is.email(emailInput.value))
      return onSubmit({ email: emailInput.value });
    // otherwise look for the username
    return onSubmit({ username: emailInput.value });
  }

  return (
    <Fragment>
      <Callout color='info'>
        Please note that you can connect "Parent accounts" as well using their accounts E-mail address or username.<br />
        To create new accounts, please go <Link to='/bm/staff'>to the staff page.</Link>
      </Callout>
      <Row className='NewStaffRow'>
        <Col xs='12'>
          <label>Add account by E-mail address or username.</label>
          <InputGroup>
            <input placeholder='example@example.com / username' ref={emailRef} className='form-control' />
            <Button onClick={onClick} color='primary' outline tabIndex={0}>
              <FontAwesome icon='user-plus' /> Connect Account
            </Button>
          </InputGroup>
        </Col>
      </Row>
    </Fragment>
  )
}

NewStaffRow.propTypes = {
  onSubmit: PropTypes.func.isRequired
}
