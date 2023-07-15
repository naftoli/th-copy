import React, { Component, Fragment } from 'react';
// components
import { ShippingRow } from '../rows';
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { Input, TabPane } from 'reactstrap';
import { Callout } from 'components/ui';
// functions
import { onInputChange } from 'functions/events';
import { NavigationRow } from '../rows/registration/NavigationRow';

export class ShippingTab extends Component {

  onChange = e => {
    e.persist()
    onInputChange( this.props.onUpdate )( e );
  }

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

    const hideShipping = shipping_method === 'pickup';
    // default props
    // render the page
    return (
      <TabPane tabId={ tabId }>
        <Form id='ShippingTab'
          onSubmit={ onSubmit }
          onValidChange={ onValidChange }
          validateAfterSubmit={ !!back }>

          <Callout color="warning">
            Tzivos Hashem HQ sends out medals, rank books, magazines, and other items for your chayolim approximately once monthly.
            Please indicate whether you would like yours to be shipped to your school or prepared for pickup from our Crown Heights warehouse.
          </Callout>

          <ShippingRow
            required={ required }
            onChange={ this.onChange }
            shipping_last={ shipping_last }
            shipping_first={ shipping_first }
            shipping_method={ shipping_method } />

          <AddressRow
            showPhone
            hideShipping={ hideShipping }
            { ...base }
            title={ 'School Shipping Address' }
            prefix='shipping_'
            required={ required }
            onChange={ this.onChange } />

          <br />
          { !hideShipping &&
          <Callout color="warning">
            We need to have an alternate residential address for the times that we send out material that will not arrive in your school during school days.
          </Callout>
          }

          <AddressRow
            hideShipping={ hideShipping }
            { ...base }
            title={ 'Residential Shipping Address' }
            prefix='res_'
            required={ required }
            onChange={ this.onChange } />

          { !hideShipping &&
          <Fragment>
            <p className='title'>
              Special Shipping Requests
            </p>
          </Fragment>
          }

          <label>Shipping Notes</label>
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
