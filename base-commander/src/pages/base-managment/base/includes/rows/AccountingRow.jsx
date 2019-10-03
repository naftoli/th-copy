import React, { Component } from 'react';
import { Row, Col, Input } from 'reactstrap';
import { PhoneNumber } from 'components/inputs';
import memoize from 'memoize-one';

export class AccountingRow extends Component {

  getName = memoize( key => {
    return `${this.props.prefix}${key}`
  });

  getValue = memoize( key => {
    return this.props[`${this.props.prefix}${key}`] || '';
  });

  render() {
    const { getName, getValue } = this;
    const { title, disabled, required, onChange } = this.props;
    const inputProps = { disabled, required, onChange };
    
    return (
          <Row id='accounting-row'>

            { title && 
              <Col xs={ 12 }>
                <p className='title'>{ title }</p>
              </Col>
            }

            <Col xs={ 6 }>
              <label>Name of contact</label>
              <Input name={ getName('name') } id={ getName('name') } value={ getValue('name') } { ...inputProps } />
            </Col>

            <Col xs={ 6 }>
              <label>Phone</label>
              <PhoneNumber name={ getName('number')  } id={ getName('number')  } 
                value={ getValue('number')  } { ...inputProps } />
              <div className='invalid-message'>Please enter a valid phone number</div>
            </Col>

            <Col xs={ 6 }>
              <label>Email</label>
              <Input type='email' name={ getName('email') } id={ getName('email') } value={ getValue('email') } { ...inputProps } />
            </Col>
          </Row>
    );
  }
}
