import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Row, Col, Input } from 'reactstrap';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { PhoneNumber, Password } from 'components/inputs';
// functions
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';

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
    console.log( this.props.account.admin_id, this.state.updates );
  }

  render() {
    let { account } = this.props;

    account = { ...account, ...this.state.updates };
    const updated = Object.keys( this.state.updates ).length > 0;
    const { 
      username, password, title, first, last, admin_email,
      admin_phone_work, admin_phone_mobile, logins, ...address
    } = account;

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
            <Password name='password' value={ password } {...inputProps} required />
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
        
        <pre>
          { JSON.stringify( this.props.account.logins, null, 2 ) }
        </pre>
      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  account: login.current_user
})

export default connect( mapStateToProps )( AccountPage );