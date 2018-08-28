import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { BaseLogo, FontAwesome } from 'components/ui';
import { PhoneNumber, Password } from 'components/inputs';
import { Row, Col, Input, Button, Card } from 'reactstrap';
// state
import { updateCurrentUser } from 'store/login/operations';
// functions
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
// style
import './AccountPage.scss';

class Account extends Component {
  render() {
    const { name, img } = this.props;
    return (
      <Card className='Account'>
        <Row>
          <Col xs={3} xl={2}>
            <BaseLogo src={ img } />
          </Col>
          <Col xs={5} xl={6}>
            <span className='name'>{ name }</span>
          </Col>
          <Col xs={4}>
            <Button color='danger'>
              <FontAwesome icon='trash'/> Remove
            </Button>
          </Col>
        </Row>
      </Card>
    );
  }
}

class AccountPage extends Component {

  state = { updates: {} };

  componentDidMount() { setTitle( 'My Account' ); }

  // event handlers
  handleUpdates = updates => {
    updates = filterUpdates( this.props.account, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  };
  onChange = ({ target }) => { this.handleUpdates({ [target.name]: target.value }) };
  update = () => {
    this.props.updateCurrentUser( this.state.updates )
    .then( account => { this.setState({ updates: {} }); });
  }

  render() {
    let { account } = this.props;

    account = { ...account, ...this.state.updates };
    const updated = Object.keys( this.state.updates ).length > 0;
    let { 
      username, password, title, first, last, admin_email,
      admin_phone_work, admin_phone_mobile, logins, customerProfile, ...address
    } = account;

    logins = logins.filter(
      login => [ 'INST', 'BC', 'TEACHER' ].includes( login.code )
    );

    const inputProps = {
      onChange: this.onChange
    };

    return (
      <div id='AccountPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />

        <p className='title'>Login Information</p>
        <Row>
          <Col xs={12} sm={6}>
            <label>Username</label>
            <Input name='username' value={ username } {...inputProps} required />
          </Col>
          <Col xs={12} sm={6}>
            <label>New Password</label>
            <Password name='password' value={ password } {...inputProps} tabToggle required />
          </Col>
        </Row>

        <p className='title'>Personal Information</p>
        <Row>
          <Col xs={4} sm={3}>
            <label>Title</label>
            <Input name='title' value={ title } {...inputProps} />
          </Col>

          <Col xs={8} sm={4}>
            <label>First Name</label>
            <Input name='first' value={ first } {...inputProps} />
          </Col>

          <Col xs={12} sm={5}>
            <label>Last Name</label>
            <Input name='last' value={ last } {...inputProps} />
          </Col>

          <Col xs={12}>
            <label>E-Mail Address</label>
            <Input name='admin_email' type='email' value={ admin_email } {...inputProps} />
            <div className='invalid-message'>Please enter a valid E-mail address</div>
          </Col>

          <Col xs={12} sm={6}>
            <label>Work Phone</label>
            <PhoneNumber name='admin_phone_work' value={ admin_phone_work } {...inputProps} />
            <div className='invalid-message'>Please enter a valid phone number</div>
          </Col>

          <Col xs={12} sm={6}>
            <label>Cell Phone</label>
            <PhoneNumber name='admin_phone_mobile' value={ admin_phone_mobile } {...inputProps} />
            <div className='invalid-message'>Please enter a valid phone number</div>
          </Col>
        </Row>

        <AddressRow { ...address } prefix='admin_' title={ false } onChange={ this.onChange } />

        <SaveButton show={ updated } onClick={ this.update } />

        <p className='title'>Account Access</p>
        <div id='accounts'>
          { logins.map( ( login, index ) => <Account key={ index } { ...login } /> )}
        </div>
      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  account: login.current_user
})

const mapDispatchToProps = {
  updateCurrentUser
}

export default connect( mapStateToProps, mapDispatchToProps )( AccountPage );
