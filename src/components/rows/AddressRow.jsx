import React from 'react';
import { Row, Col, Input } from 'reactstrap';

export const AddressRow = ({ address, city, state, zip, country, disabled, onChange, showTitle = false }) => {
  const inputProps = { disabled: disabled, onChange: onChange };
  return (
    <Row id='address-row'>
      { showTitle && 
        <Col xs='12'>
          <p className='title'>Address</p>
        </Col>
      }
      <Col xs='12'>
        <label>Address</label>
        <Input value={ address } {...inputProps} />
      </Col>
      <Col xs='6'>
        <label>City</label>
        <Input value={ city } {...inputProps} />
      </Col>
      <Col xs='3'>
        <label>State</label>
        <Input value={ state } {...inputProps} />
      </Col>
      <Col xs='3'>
        <label>Zip</label>
        <Input value={ zip } {...inputProps} />
      </Col>
      <Col xs='6'>
        <label>Country</label>
        <Input value={ country } {...inputProps} />
      </Col>
    </Row>
  );
}
