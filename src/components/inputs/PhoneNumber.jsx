import React from 'react';
import Cleave from 'cleave.js/react';
// To large, we need to make a custom smaller version once we can
import 'cleave.js/dist/addons/cleave-phone.i18n';

export const PhoneNumber = ( props ) => 
  <Cleave 
    type='tel'
    { ...props }
    className='form-control'
    options={{ phone: true, phoneRegionCode: 'US' }}
    title='Please enter a valid 9+ digit Phone Number'
    pattern='^(\+[0-9]{1,3}[0-9 ]{9,})|((?:1 )?[0-9]{3}(?: )?[0-9]{3}(?: )?[0-9]{4})$' />
