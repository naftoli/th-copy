import React from 'react';
import MaskedInput from 'react-text-mask';
// data
import masks from 'components/masks';

export const PhoneNumber = ( props ) => 
  <MaskedInput 
    {...props} 
    mask={ masks.phone } 
    className='form-control'
    pattern='^\([0-9]{3}\) [0-9]{3}-[0-9]{4}$' 
    title='Please enter a valid Phone Number' />
