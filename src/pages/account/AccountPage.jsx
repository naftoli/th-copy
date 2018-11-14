import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Account } from './includes/Account';
import { SaveButton } from 'components/buttons';
import { LoginRow, InformationRow } from './includes/Rows';
// state
import { removeAuth } from 'store/base/staff/operations';
import { 
  connectChabad,  disconnectChabad,
  getCurrentUser, updateCurrentUser,
} from 'store/login/operations';
// functions
import { setTitle } from 'functions/utils';
import { showError } from 'functions/notifications';
import { filterUpdates, onInputChange } from 'functions/events';
// style
import './AccountPage/AccountPage.scss';
import { toast } from 'react-toastify';

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

  // * connect account to chabad.org
  connectToChabadOrg = key => showError(
    this.props.connectChabad( key )
    .then( () => toast.info( 'Chabad.org Login Connected' ) )
  );

  // * disconnect from chabad.org account
  disconnectChabad = () => showError(
    this.props.disconnectChabad()
    .then( () => toast.info( 'Chabad.org Login Removed' ) )
  );

  render() {
    const { updates, saving } = this.state;
    let { account } = this.props; // load the account and the updates
    account = { ...account, ...updates };
    // and check if any of the updates require saving
    const updated = Object.keys( updates ).length > 0;
    // extract props
    let {
      username, title,  first,    admin_email,   last,
      logins,   admin_phone_work, admin_phone_mobile,
      chabad_org_shliach_id
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
          shliach_id={ chabad_org_shliach_id }
          onChabadOrgLogin={ this.connectToChabadOrg }
          onChabadDisconnect={ this.disconnectChabad } />

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

        <h4>Account Access</h4>

        <div id='accounts'>
          { logins.map( ( login, index ) => 
            <Account 
              { ...login }
              key={ index }
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
  connectChabad,      removeAuth,
  disconnectChabad,   getCurrentUser,   updateCurrentUser,
}

export default connect(
  mapStateToProps, mapDispatchToProps
)( AccountPage );
