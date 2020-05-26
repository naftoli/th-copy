import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import Mosdos from './includes/Mosdos';
// import { Link } from 'react-router-dom';
// import { FontAwesome, Callout } from 'components/ui';
import { Callout } from 'components/ui';
import { AccountRow } from 'components/rows';
import { Label, Password } from 'components/inputs';
import { Row, Col, Collapse, UncontrolledAlert, Input } from 'reactstrap';
import { SaveButton } from 'components/buttons';
// import { GoogleButton, ChabadOrgButton, SaveButton } from 'components/buttons';
// styles and images
import logo from 'img/logos/th.svg';
// functions
import { onInputChange } from 'functions/events';
import { login, getCurrentUser, createAccount } from 'store/login/operations';

class NewAccount extends Component {

  state = {
    account: {},
    saving: false,
    errors: []
  }
  // handle the change
  updateAccount = updates =>
    this.setState({ account: { ...this.state.account, ...updates } });
  // onchange event handler
  onChange = onInputChange( this.updateAccount );

  // * attempt to login the user if they use a "login with" service
  login = ( opts ) => {
    // if the login works redirect them to their account page to make edits
    return this.props.login( opts )
      .then( () => this.props.history.replace('/myaccount') )
      .then( this.props.getCurrentUser );
  }

  // * handle google login information
  onGoogleLogin = google => {
    // attempt to log them in
    this.login({ google_key: google.token_id })
    // and if it fails, autofill the form
    .catch( () => {
      const profile = google.getBasicProfile();

      this.updateAccount({
        last: profile.getFamilyName(),
        first: profile.getGivenName(),
        admin_email: profile.getEmail(),
        google_id: google.google_id
      });
    });
  }
  // * handle login with chabad.org
  onChabadOrgLogin = chabad_key => this.login({ chabad_key })
    // and if it fails, update the form
    .catch( response => {
      if ( !response.data ) return false;
      // extract variables we need to clean
      let { shliachID, phone, mosdos, ...shliach } = response.data;
      // set selected to fals on all the mosdos
      mosdos = mosdos && mosdos.map( m => ({ ...m, selected: true }) );
      // update the state
      this.updateAccount({
        ...shliach, mosdos,
        chabad_org_shliach_id: shliachID,
        admin_phone_work: phone
      });
    });
  // turn a mosad on or off
  selectMosad = id => {
    let { mosdos } = this.state.account;
    // return false if there are no mosdos
    if ( !Array.isArray( mosdos ) )
      return false;
    // toggle the mosad to be checked/unchecked
    mosdos = mosdos.map( m => {
      if ( m.id === id )
        return { ...m, selected: !m.selected }
      return m;
    });
    this.updateAccount({ mosdos });
  }

  // * create the new account
  onSubmit = e => {
    e && e.preventDefault();
    // setup the request
    const { account } = this.state;
    this.setState({ errors: [], saving: true });
    // make the request to create an account and wait for the response
    this.props.createAccount( account )
      .then( () => {
        this.props.history.replace('/register')
      })
      .catch( e => {
        this.setState({ saving: false, errors: e.data || [ e.message ] });
      });
  }

  // check if username is unique
  checkUsername = e => {
    if ( e.target.value.length ) {
      const url = process.env.NODE_ENV === "production" ? "" : "//tzivos.local";
      fetch(url + '/ajax/checkDuplicateEmail.php?email=' + e.target.value)
      .then( result => {
        return result.json()
      })
      .then( data => {
        console.log( data )
        if ( data === 1 ) {
          alert("This email address is already being used. Please choose a different one.");
          this.setState({ account: { ...this.state.account, admin_email: '' } })
        }
      })
      .catch( error => {
        console.log( error )
      })
    }
  }

  render() {
    let { account, errors, saving } = this.state;

    errors = errors.map( ( e, i ) =>
      <UncontrolledAlert color='danger' key={ i }>{ e }</UncontrolledAlert>
    );

    return (
      <div id='NewAccount'>
        <img src={logo} id='logo' alt='logo' />

        <h1>Tzivos Hashem</h1>
        
        <h3>New Base Commander Account</h3>
        <br />

        <form onSubmit={ this.onSubmit } id='create-form'>

          <div id='account-login'>
            <p className='title'>Create a Login</p>
            <Row>
              {/* <Col xs={12} id='sign-in-with'>

                <GoogleButton
                  disabled={ !!account.google_id }
                  onSuccess={ this.onGoogleLogin } />

                <ChabadOrgButton
                  onLogin={ this.onChabadOrgLogin }
                  disabled={ !!account.chabad_org_shliach_id } />

              </Col> */}

              <Col xs={12}>
                <Label>E-mail Address (this will be your username)</Label>

                <Input
                  required
                  autoFocus
                  type='email'
                  name='admin_email'
                  placeholder='Username'
                  autoComplete='username'
                  onChange={ this.onChange } 
                  onBlur={ this.checkUsername }
                  value= { account.admin_email || '' } />
              </Col>

              <Col sm={6}>
                <Label>Create a Password</Label>

                <Password required
                  name='password'
                  placeholder='Create a Password'
                  onChange={ this.onChange }
                  autoComplete='new-password'
                  value= { account.password || '' } />
              </Col>

              <Col sm={6}>
                <Label>Confirm Password</Label>

                <Password required
                  name='confirm'
                  onChange={ this.onChange }
                  autoComplete='new-password'
                  placeholder='Confirm Password'
                  value= { account.confirm || '' } />
              </Col>
            </Row>
          </div>

          <div id='account-information'>
            <p className='title'>Personal Information</p>

            <AccountRow
              account={ account }
              onChange={ this.onChange } />
          </div>
          
          <Collapse isOpen={ !!account.mosdos } id='chabad-mosdos'>
            <p className='title'>Chabad.org Mosdos</p>

            <Callout color='info'>Please select the Mosdos you wish to use in Tzivos Hashem</Callout>

            <Mosdos 
              mosdos={ account.mosdos }
              onChange={ this.selectMosad } />
          </Collapse>

          { errors }

          <Row>
            <Col xs={12} id='actions'>
              
              <SaveButton
                  saving={ saving }
                  disabled={ saving }>
                Create Account
              </SaveButton>
              
              {/* <Link className='btn btn-primary' to='/'>
                Login <FontAwesome icon='sign-in-alt' />
              </Link> */}
            </Col>
          </Row>
        </form>
      </div>
    );
  }
}

const mapDispatchToProps = {
  login, getCurrentUser, createAccount
}

export default connect( null, mapDispatchToProps )( NewAccount );
