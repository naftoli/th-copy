import React from 'react';
// components
import MaskedInput from 'react-text-mask';
import { Row, Col, Input } from 'reactstrap';
// data
import masks from 'components/masks';

const AddressRow = ( { soldier, onChange } ) => {
  const { 
    user_address1, user_city, user_state,
    user_phone, user_postal, user_country
  } = soldier;
  return (
    <Row id='address-row'>
      <Col xs='12'>
        <p className='title'>Address</p>
      </Col>
      <Col xs='12'>
        <label>Address</label>
        <Input id='user_address1' value={ user_address1 } onChange={ onChange } />
      </Col>
      <Col xs='6'>
        <label>City</label>
        <Input id='user_city' value={ user_city } onChange={ onChange } />
      </Col>
      <Col xs='3'>
        <label>State</label>
        <Input id='user_state' value={ user_state } onChange={ onChange } />
      </Col>
      <Col xs='3'>
        <label>Zip</label>
        <Input id='user_postal' value={ user_postal } onChange={ onChange } />
      </Col>
      <Col xs='6'>
        <label>Country</label>
        <Input id='user_country' value={ user_country } onChange={ onChange } />
      </Col>
      <Col xs='6'>
        <label>Phone #</label>
        <MaskedInput className='form-control' id='user_phone' value={ user_phone } 
          onChange={ onChange } mask={ masks.phone } />
      </Col>
    </Row>
  )
}

export default AddressRow;
