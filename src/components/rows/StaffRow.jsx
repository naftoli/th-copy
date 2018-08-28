import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
// components
import { Link } from 'react-router-dom';
import { FontAwesome, Callout } from 'components/ui';
import { 
  Row, Col, Button, ButtonGroup, Card, 
  Input, InputGroup, InputGroupAddon
} from 'reactstrap';
// functions
import is from 'is_js';
// styles
import './StaffRow.scss';

export class StaffRow extends Component {

  static defaultProps = {
    first: '', last: '', username: '', email: '',
    disconnect: () => {}, admin_id: 0
  }

  disconnect = () => {
    this.props.disconnect( this.props.admin_id )
  }
  
  render() {

    const { admin_id, first, last, username, email } = this.props;
    
    return (
      <Card className='StaffRow'>
        <Row>
          <Col xs='12'>
            <p className='staff-name'>
              Name: <Link to={`/bm/staff/${admin_id}`}>{last}, {first}</Link>
            </p>
          </Col>
          <Col sm='4'>
            <label>Username</label>
            <Input disabled value={username} />
          </Col>
          <Col sm='4'>
            <label>E-Mail</label>
            <Input disabled value={email} />
          </Col>
          <Col sm='4'>
            <label>Actions</label>
            <ButtonGroup>
              <Button color='danger' onClick={ this.disconnect }>
                Disconnect
              </Button>
            </ButtonGroup>
          </Col>
        </Row>
      </Card>
    );
  }
}

export class NewStaffRow extends Component {

  static propTypes = {
    onSubmit: PropTypes.func.isRequired
  }

  emailRef = React.createRef();

  onClick = e => {
    const emailInput = this.emailRef.current;
    // if nothing was entered, focus on the input
    if ( !emailInput.value ) return emailInput.focus();
    // valid emails we look for the email address
    if ( is.email( emailInput.value ) )
      return this.props.onSubmit({ email: emailInput.value });
    // otherwise look for the username
    return this.props.onSubmit({ username: emailInput.value });
  }

  render() {
    return (
      <Fragment>
        <Callout color='info'>
          Please note that you can connect "Parent accounts" as well using their accounts E-mail address or username.<br/>
          To create new accounts, please go <Link to='/bm/staff'>to the staff page.</Link>
        </Callout>
        <Row className='NewStaffRow'>
          <Col xs='12'>
            <label>Add account by E-mail address or username.</label>
            <InputGroup>
              <input placeholder='example@example.com / username' ref={ this.emailRef } className='form-control'/>
              <InputGroupAddon addonType="append">
                <Button onClick={ this.onClick } color='primary' outline tabIndex={0}>
                  <FontAwesome icon='user-plus' /> Connect Account
                </Button>
              </InputGroupAddon>
            </InputGroup>
          </Col>
        </Row>
      </Fragment>
    )
  }
}
