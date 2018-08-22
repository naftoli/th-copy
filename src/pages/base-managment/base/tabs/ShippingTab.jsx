import React, { Component } from 'react';
// components
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { Row, Col, Input, Label, TabPane } from 'reactstrap';
// functions
import { eventToUpdate } from 'functions/events';

export class ShippingTab extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  render(){
    const { updated, tabId, onSubmit, onValidChange } = this.props;
    const { 
      shipping_requests, shipping_first, shipping_last, 
      shipping_method, ...base 
    } = this.props.base;

    return (
      <TabPane tabId={ tabId }>
        <Form id='ShippingTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>
          <Row>
            <Col xs={12} sm={4}>
              <Label>Shipping Method</Label>
              <Input type="select" name='shipping_method' value={ shipping_method } onChange={ this.onChange }>
                <option value='pickup'>Pickup</option>
                <option value='deliver'>Delivery</option>
              </Input>
            </Col>
            <Col xs={6} sm={4}>
              <Label>First Name</Label>
              <Input name='shipping_first' value={ shipping_first } onChange={ this.onChange } />
            </Col>
            <Col xs={6} sm={4}>
              <Label>Last Name</Label>
              <Input name='shipping_last' value={ shipping_last } onChange={ this.onChange } />
            </Col>
          </Row>

          <AddressRow { ...base } prefix='shipping_' 
            onChange={ this.onChange } title={ false } />

          <p className='title'>Special Shipping Requests</p>
          <Input type="textarea" name='shipping_requests' rows='8'
            value={ shipping_requests } onChange={ this.onChange } />

          <SaveButton show={ updated } />
        </Form>
      </TabPane>
    )
  }
}
