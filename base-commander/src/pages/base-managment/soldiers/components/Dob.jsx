import React from 'react';
import moment from 'moment';
import { Col } from 'reactstrap';
import { Date } from 'components/inputs';

export const DobCol = ({ dob, onChange }) => (
  <Col sm='6'>
    <label htmlFor='dob'>Date of Birth</label>
    <Date
      id='dob' name='dob'
      onChange={ onChange } required
      value={ dob ? moment( dob ) : undefined } 
      // client side date validations
      maxDate={ moment().subtract( 5, 'years' ) }
      minDate={ moment().subtract( 20, 'years' ) } />
  </Col>
);
