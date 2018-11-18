import React, { Component } from 'react';

import { Button } from 'reactstrap';
import { GOOGLE_CLIENT_ID } from 'components/constants';

import google from 'img/logos/google.svg';

const noop = () => {};

export class GoogleButton extends Component {

  state = {
    disabled: true
  }

  static defaultProps = {
    disabled: false,
    tokenOnly: false,
    isSignedIn: false,
    onSuccess: noop,
    onFailure: noop
  }

  componentDidMount() {
    const { isSignedIn, onFailure } = this.props;

    const params = {
      client_id: GOOGLE_CLIENT_ID
    }

    window.gapi && window.gapi.load('auth2', () => {
      this.enableButton();
      // if the api is not loaded up, load it up
      if ( !window.gapi.auth2.getAuthInstance() ) {
        window.gapi.auth2.init( params ).then(
          res => {
            if ( isSignedIn && res.isSignedIn.get() )
              this.handleSigninSuccess(res.currentUser.get());
          }, err => onFailure( err )
        );
      };
    });
  };

  // enable the button once google's api has been loaded up
  enableButton = () => this.setState({
    disabled: this.props.disabled || false
  });

  signIn = e => {
    e && e.preventDefault();
    // don't do anything if this is disabled
    if ( this.state.disabled )
      return false;

    const auth2 = window.gapi.auth2.getAuthInstance();
    auth2.signIn()
    .then( res => this.handleSigninSuccess( res ) )
    .catch( err => this.props.onFailure( err ) );
  }

  // * format the response object
  handleSigninSuccess = ( res ) => {
    const basicProfile = res.getBasicProfile();
    const authResponse = res.getAuthResponse();
    // get the params the way we want
    res.token_id = authResponse.id_token;
    res.google_id = basicProfile.getId();
    // allow option to only send token id for server confirmation
    if ( this.props.tokenOnly )
      return this.props.onSuccess( res.token_id );
    return this.props.onSuccess( res );
  }

  // clear state updates when component unmounts
  componentWillUnmount() {
    this.enableButton = () => {};
  }

  // render the page
  render() {
    const { size } = this.props;
    const { disabled } = this.state;

    return (
      <Button
        size={ size }
        disabled={ disabled }
        onClick={ this.signIn }
        className='GoogleButton'>
  
        <img 
          alt='google' 
          src={ google } />
          
        <span>Sign in with Google</span>
      </Button>
    );
  }
}