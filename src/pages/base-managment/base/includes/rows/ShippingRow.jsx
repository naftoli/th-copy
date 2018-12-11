import React, { Component } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';

export class ShippingRow extends Component {

  render(){
    const {
      required,   shipping_first,
      onChange,   shipping_method,  shipping_last
    } = this.props;

    const inputProps = { required, onChange };

    return (
      <Row>
        <Col xs={12} sm={4}>
          <label>Shipping Method</label>
          <Input type="select"    name='shipping_method'
              { ...inputProps }   value={ shipping_method }>
            <option value='pickup'>Pickup</option>
            <option value='deliver'>Delivery</option>
          </Input>
        </Col>
        <Col xs={6} sm={4}>
          <label>First Name</label>
          <Input { ...inputProps }
            name='shipping_first'
            value={ shipping_first || '' } />
        </Col>
        <Col xs={6} sm={4}>
          <label>Last Name</label>
          <Input { ...inputProps }
            name='shipping_last' 
            value={ shipping_last || '' } />
        </Col>
      </Row>
    );
  }
}
