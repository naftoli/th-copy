import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Account } from './includes/Account';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { LoginRow, AccountRow } from './includes/Rows';
// state
import { removeAuth } from 'store/base/staff/operations';
import { updateCurrentUser, getCurrentUser } from 'store/login/operations';
// functions
import { setTitle } from 'functions/utils';
import { showError } from 'functions/notifications';
import { filterUpdates, onInputChange } from 'functions/events';
// style
import './includes/AccountPage.scss';

class AccountPage extends Component {

  state = {
    updates: {}
  };

  componentDidMount() {
    setTitle( 'My Account' );
    this.props.getCurrentUser();
  }

  // * handle updates from the UI
  handleUpdates = updates => {
    // we do not load the password, so leave it blank by default
    const account = { ...this.props.account, password: '' }
    // filter updates for any changes
    updates = filterUpdates( account, { ...this.state.updates, ...updates } );
    // and update the state
    this.setState({ updates });
  };

  onChange = onInputChange( this.handleUpdates );

  // * submit the event
  onSubmit = event => {
    event.preventDefault();
    // do not double submit
    if ( this.state.saving )
      return true;
    // update the state
    this.setState({ saving: true });
    // update the user, showing any errors
    showError(
      this.props.updateCurrentUser( this.state.updates )
        .then( () => this.setState({ updates: {} }) )
    )
    .then( () => this.setState({ saving: false }) );
  }

  // * disconnect logins
  disconnect = ( auth, id ) => {
    const { admin_id } = this.props.account;
    this.props.removeAuth({ auth, id, admin_id })
      .then( this.props.getCurrentUser );
  }

  // * do not update when only these keys have changed
  filterKeys = key => ![ 'old_password' ].includes( key );

  render() {
    const { updates, saving } = this.state;
    let { account } = this.props; // load the account and the updates
    account = { ...account, ...updates };
    // and check if any of the updates require saving
    const updated = Object.keys( updates )
      .filter( this.filterKeys ).length > 0;
    // extract props
    let {
      username, password,   old_password,
      title,    first,      admin_email,
      logins,   last,       admin_phone_work,
      admin_phone_mobile,   ...address
    } = account;
    // *only show some logins
    logins = logins.filter(
      login => [ 'INST', 'BC', 'TEACHER' ].includes( login.code )
    );

    return (
      <div id='AccountPage'>
        <Prompt when={ updated } 
          message="You have unsaved changes. Are you sure you want to leave?" />
        
        <form onSubmit={ this.onSubmit }>
          <p className='title'>
            Login Information
          </p>

          <LoginRow
            username={ username }
            password={ password }
            onChange={ this.onChange }
            old_password={ old_password } />

          <p className='title'>
            Personal Information
          </p>

          <AccountRow
            first={ first }
            last={ last }
            title={ title }
            onChange={ this.onChange }
            admin_email={ admin_email }
            admin_phone_work={ admin_phone_work }
            admin_phone_mobile={ admin_phone_mobile } />

          <AddressRow
            { ...address } 
            prefix='admin_'
            title={ false }
            onChange={ this.onChange } />

          <SaveButton
            show={ updated }
            saving={ saving }
            disabled={ saving } />
        </form>

        <p className='title'>
          Account Access
        </p>

        <div id='accounts'>
          { logins.map( ( login, index ) => 
            <Account key={ index } { ...login }
              disconnect={ this.disconnect } />
          ) }
        </div>
      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  account: login.current_user
})

const mapDispatchToProps = {
  updateCurrentUser, getCurrentUser, removeAuth
}

export default connect(
  mapStateToProps, mapDispatchToProps
)( AccountPage );
