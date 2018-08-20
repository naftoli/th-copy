import React, { Component } from 'react';
import { Row, Col, Input } from 'reactstrap';
import { PhoneNumber } from 'components/inputs';

export class AddressRow extends Component {

  static defaultProps = {
    title: 'Address',
    prefix: '',
  }

  getName = ( key ) => {
    return `${this.props.prefix}${key}`;
  }

  getValue = ( key ) => {
    return this.props[`${this.props.prefix}${key}`] || '';
  }

  render() {
    const { getName, getValue } = this;
    const { title, disabled, onChange } = this.props;
    const inputProps = { disabled, onChange };
    return (
      <Row id='address-row'>
        { title && 
          <Col xs='12'>
            <p className='title'>{ title }</p>
          </Col>
        }
        <Col xs='12'>
          <label>Address 1</label>
          <Input name={ getName( 'address1' ) } id={ getName( 'address1' ) } 
            value={ getValue( 'address1' ) } {...inputProps} />
        </Col>
        <Col xs='12'>
          <label>Address 2</label>
          <Input name={ getName( 'address2' ) } id={ getName( 'address2' ) }
            value={ getValue( 'address2' ) } {...inputProps} />
        </Col>
        <Col xs='6'>
          <label>City</label>
          <Input name={ getName( 'city' ) } id={ getName( 'city' ) }
            value={ getValue( 'city' ) } {...inputProps} />
        </Col>
        <Col xs='3'>
          <label>State</label>
          <Input name={ getName( 'state' ) } id={ getName( 'state' ) }
            value={ getValue( 'state' ) } {...inputProps} />
        </Col>
        <Col xs='3'>
          <label>Zip</label>
          <Input name={ getName( 'postal' ) } id={ getName( 'postal' ) }
            value={ getValue( 'postal' ) } {...inputProps} />
        </Col>
        <Col xs='6'>
          <label>Country</label>
          <Input name={ getName( 'country' ) } id={ getName( 'country' ) } 
            value={ getValue( 'country' ) } {...inputProps} />
        </Col>
        <Col xs='6'>
          <label>Phone</label>
          <PhoneNumber name={ getName( 'phone' ) } id={ getName( 'phone' ) } 
            value={ getValue( 'phone' ) } {...inputProps} />
          <div className='invalid-message'>Please enter a valid phone number</div>
        </Col>
      </Row>
    );
  }
}
