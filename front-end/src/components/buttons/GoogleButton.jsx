import React, { useState, useEffect } from 'react';

import classnames from 'classnames';
import { Button } from 'reactstrap';
import { GOOGLE_CLIENT_ID } from 'components/constants';
import google from 'img/logos/google.svg';

const noop = () => { };

export const GoogleButton = ({
  disabled: disabledProp = false,
  tokenOnly = false,
  isSignedIn = false,
  onSuccess = noop,
  onFailure = noop,
  size, shrink, className: classNameProp
}) => {

  const [disabled, setDisabled] = useState(true);

  // * format the response object
  const handleSigninSuccess = (res) => {
    const basicProfile = res.getBasicProfile();
    const authResponse = res.getAuthResponse();
    // get the params the way we want
    res.token_id = authResponse.id_token;
    res.google_id = basicProfile.getId();
    // allow option to only send token id for server confirmation
    if (tokenOnly)
      return onSuccess(res.token_id);
    return onSuccess(res);
  }

  // enable the button once google's api has been loaded up
  const enableButton = () => setDisabled(disabledProp || false);


  useEffect(() => {
    const params = {
      client_id: GOOGLE_CLIENT_ID
    }

    if (window.gapi) {
      window.gapi.load('auth2', () => {
        enableButton();
        // if the api is not loaded up, load it up
        if (!window.gapi.auth2.getAuthInstance()) {
          window.gapi.auth2.init(params).then(
            res => {
              if (isSignedIn && res.isSignedIn.get())
                handleSigninSuccess(res.currentUser.get());
            }, err => onFailure(err)
          );
        };
      });
    }
  }, []);

  useEffect(() => {
    setDisabled(disabledProp);
  }, [disabledProp]);


  const signIn = e => {
    e && e.preventDefault();
    // don't do anything if this is disabled
    if (disabled)
      return false;

    const auth2 = window.gapi.auth2.getAuthInstance();
    auth2.signIn()
      .then(res => handleSigninSuccess(res))
      .catch(err => onFailure(err));
  }

  const className = classnames({
    'shrink': shrink,
    'GoogleButton': true,
    [classNameProp]: classNameProp
  });

  return (
    <Button
      size={size}
      disabled={disabled}
      onClick={signIn}
      className={className}>

      <img
        alt='google'
        src={google} />

      <span className='sign-in-with'>Sign in with&nbsp;</span>
      <span>Google</span>
    </Button>
  );
}