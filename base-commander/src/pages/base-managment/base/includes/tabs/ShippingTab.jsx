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

  state = {
    showShipping: true
  }

  onChange = onInputChange( this.props.onUpdate );
  
  onBlur = e => {
    console.log("onBlur called");
    if ( e.target.name === 'shipping_method' && e.target.value === 'pickup' ) {
      this.setState({ showShipping: false });
    } else if ( this.state.showShipping === false && e.target.name === 'shipping_method' && e.target.value === 'deliver' ) {
      this.setState({ showShipping: true });
    }
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
            onBlur={ this.onBlur }
            shipping_last={ shipping_last }
            shipping_first={ shipping_first }
            shipping_method={ shipping_method } />

          { this.state.showShipping &&
          <Fragment>
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
          </Fragment>
          }

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
