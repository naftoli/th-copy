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
    const { title, disabled, onChange, showPhone } = this.props;
    const inputProps = { disabled, onChange };
    return (
      <Row id='address-row'>

        { title && 
          <Col xs={ 12 }>
            <p className='title'>{ title }</p>
          </Col>
        }

        <Col xs={ 12 }>
          <label>Address 1</label>
          <Input name={ getName( 'address1' ) } id={ getName( 'address1' ) } placeholder='792 Eastern Parkway'
            value={ getValue( 'address1' ) } {...inputProps} maxLength={ 255 } />
          {/* <div className='invalid-message'>Please enter a valid address</div> */}
        </Col>

        <Col xs={ 12 }>
          <label>Address 2</label>
          <Input name={ getName( 'address2' ) } id={ getName( 'address2' ) } placeholder='5th Floor'
            value={ getValue( 'address2' ) } {...inputProps} maxLength={ 255 } />
        </Col>

        <Col xs={ 6 }>
          <label>City</label>
          <Input name={ getName( 'city' ) } id={ getName( 'city' ) }
            value={ getValue( 'city' ) } {...inputProps} placeholder='Brooklyn'
            pattern='^.{3,}$' title="3 or more letters" maxLength={ 255 } />
          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>

        <Col xs={ 3 }>
          <label>State</label>
          <Input name={ getName( 'state' ) } id={ getName( 'state' ) }
            value={ getValue( 'state' ) } {...inputProps } placeholder='NY'
            pattern='^[A-Za-z\s]{2,255}$' title="3 to 255 letters" maxLength={ 255 } />
          <div className='invalid-message'>Please enter a valid state</div>
        </Col>

        <Col xs={ 3 }>
          <label>Zip</label>
          <Input name={ getName( 'postal' ) } id={ getName( 'postal' ) }
            value={ getValue( 'postal' ) } {...inputProps} placeholder='11213'
            pattern='^.{3,255}$' title="3 to 255 letters" maxLength={ 255 } />
          <div className='invalid-message'>Please enter 3 to 255 letters</div>
        </Col>

        <Col xs={ showPhone ? 6 : 12 }>
          <label>Country</label>
          <Input name={ getName( 'country' ) } id={ getName( 'country' ) } 
            value={ getValue( 'country' ) } {...inputProps} placeholder='USA'
            pattern='^.{3,255}$' title="3 to 255 letters" />
          <div className='invalid-message'>Please enter 3 to 255 letters</div>
        </Col>

        { showPhone && 
          <Col xs={ 6 }>
            <label>Phone</label>
            <PhoneNumber name={ getName( 'phone' ) } id={ getName( 'phone' ) } 
              value={ getValue( 'phone' ) } {...inputProps} placeholder='(718) 467-6630'/>
            <div className='invalid-message'>Please enter a valid phone number</div>
          </Col>
        }
      </Row>
    );
  }
}
