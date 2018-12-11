import React, { Component } from 'react';
// components
import { ShippingRow } from '../rows';
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { Input, TabPane } from 'reactstrap';
// functions
import { onInputChange } from 'functions/events';
import { NavigationRow } from '../rows/registration/NavigationRow';

export class ShippingTab extends Component {

  onChange = onInputChange( this.props.onUpdate );

  render(){
    // load the props
    const {
      updated, tabId, onSubmit,
      onValidChange, back, required
    } = this.props;
    // load the base
    const { 
      shipping_first,   shipping_last, 
      shipping_method,  shipping_requests,  ...base
    } = this.props.base;
    // default props
    // render the page
    return (
      <TabPane tabId={ tabId }>
        <Form id='ShippingTab'
          onSubmit={ onSubmit }
          onValidChange={ onValidChange }
          validateAfterSubmit={ !!back }>

          <ShippingRow
            required={ required }
            onChange={ this.onChange }
            shipping_last={ shipping_last }
            shipping_first={ shipping_first }
            shipping_method={ shipping_method } />

          <AddressRow
            showPhone
            { ...base }
            title={ false }
            prefix='shipping_'
            required={ required }
            onChange={ this.onChange } />

          <p className='title'>
            Special Shipping Requests
          </p>

          <Input type="textarea" name='shipping_requests' rows='8'
            value={ shipping_requests || '' } onChange={ this.onChange } />

          { !back &&
            <SaveButton show={ updated } />
          }

          { back &&
            <NavigationRow next back={ back } />
          }
          
        </Form>
      </TabPane>
    )
  }
}
