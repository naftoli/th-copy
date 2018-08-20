import React, { Component } from 'react';
// components
import { AddressRow } from 'components/rows';
import { Input } from 'reactstrap';
// functions
import { eventToUpdate } from 'functions/events';

export class ShippingTab extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  render(){
    const { base } = this.props;

    return (
      <div id='ShippingTab'>

        <AddressRow { ...base } prefix='shipping_' 
          onChange={ this.onChange } title={ false } />

        <p className='title'>Special Shipping Requests</p>
        <Input type="textarea" name='shipping_requests' rows='9'
          value={ base.shipping_requests } onChange={ this.onChange } />

      </div>
    )
  }
}
