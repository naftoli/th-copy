import React from 'react';
import { Button } from 'reactstrap';

import google from 'img/logos/google.svg'

export const GoogleButton = props => {
  return (
    <Button
      { ...props }
      className='GoogleButton'>

      <img 
        alt='google' 
        src={ google } />
        
      <span>Sign in with Google</span>
    </Button>
  );
}