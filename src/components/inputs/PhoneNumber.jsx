import React, { Component } from 'react';
import MaskedInput from 'react-text-mask';
// data
import masks from 'components/masks';

export const PhoneNumber = ( props ) => {
  return <MaskedInput {...props} mask={ masks.phone } className='form-control'/>
}