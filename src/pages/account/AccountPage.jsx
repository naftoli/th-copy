import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { SaveButton } from 'components/buttons';
import { LoginRow, InformationRow, AccessRow } from './includes/Rows';
// state
import { removeAuth } from 'store/base/staff/operations';
import { 
  connectExternalAccount,     getCurrentUser,
  disconnectExternalAccount,  updateCurrentUser,
} from 'store/login/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { showError } from 'functions/notifications';
import { filterUpdates, onInputChange } from 'functions/events';
// style
import './AccountPage/AccountPage.scss';
import LoginModal from './LoginModal';

class AccountPage extends Component {
  // default state
  state = {
    updates: {},
    saving: false,
    isOpen: false, // is the username & password modal open or not
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

  // * toggle the modal
  toggle = () => this.setState({
    isOpen: !this.state.isOpen
  });

  // * disconnect logins
  disconnectLogin = ( auth, id ) => {
    const { admin_id } = this.props.account;
    this.props.removeAuth({ auth, id, admin_id })
      .then( this.props.getCurrentUser );
  }
  // * connect to an external account
  connectExternalAccount = type => key => showError(
    this.props.connectExternalAccount( type, key )
    .then( () => toast.info(`${type} account connected`) )
  );
  // * disconnect from an external account
  disconnectExternalAccount = type => () => showError(
    this.props.disconnectExternalAccount( type )
    .then( () => toast.info(`${type} account removed`) )
  );

  render() {
    const { updates, saving, isOpen } = this.state;
    let { account } = this.props; // load the account and the updates
    account = { ...account, ...updates };
    // and check if any of the updates require saving
    const updated = Object.keys( updates ).length > 0;
    // extract props
    let {
      username, title,  first,    admin_email,   last,
      logins,   admin_phone_work, admin_phone_mobile,
      chabad_org_shliach_id,      google_id
    } = account;

    // * only show some logins
    logins = logins.filter(
      login => [ 'INST', 'BC', 'TEACHER' ].includes( login.code )
    );

    return (
      <div id='AccountPage'>
        <Prompt when={ updated } 
          message="You have unsaved changes. Are you sure you want to leave?" />
        
        <h1>My Tzivos Hashem Account</h1>

        <hr/>

        <h4>Login Methods</h4>

        <LoginRow
          username={ username }
          google_id={ google_id }
          shliach_id={ chabad_org_shliach_id }

          editPassword={ this.toggle }
          onConnect={ this.connectExternalAccount }
          onDisconnect={ this.disconnectExternalAccount } />

        <hr/>

        <h4>Account Information</h4>

        <form onSubmit={ this.onSubmit }>
          <InformationRow
            first={ first }
            last={ last }
            title={ title }
            onChange={ this.onChange }
            admin_email={ admin_email }
            admin_phone_work={ admin_phone_work }
            admin_phone_mobile={ admin_phone_mobile } />

          <SaveButton
            show={ updated }
            saving={ saving }
            disabled={ saving } />
        </form>

        <hr/>

        { logins.length > 0 && 
          <h4>Account Access</h4>
        }

        <AccessRow
          logins={ logins }
          disconnect={ this.disconnectLogin } />

        <LoginModal
          isOpen={ isOpen }
          toggle={ this.toggle }
          username={ username } />

      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  account: login.current_user
})

const mapDispatchToProps = {
  connectExternalAccount,     getCurrentUser,
  disconnectExternalAccount,  updateCurrentUser,  removeAuth,
}

export default connect(
  mapStateToProps, mapDispatchToProps
)( AccountPage );
