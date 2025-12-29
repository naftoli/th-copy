import React, { Fragment } from 'react';
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

export const ShippingTab = ({
  updated,
  tabId,
  onSubmit,
  onValidChange,
  back,
  required,
  onUpdate,
  base
}) => {

  const onChange = e => {
    e.persist()
    onInputChange(onUpdate)(e);
  }

  // load the base
  const {
    shipping_first, shipping_last, shipping_phone,
    shipping_method, shipping_requests, chidon_first, chidon_last, ...restBase
  } = base;

  const hideShipping = shipping_method === 'pickup';
  // default props
  // render the page
  return (
    <TabPane tabId={tabId}>
      <Form id='ShippingTab'
        onSubmit={onSubmit}
        onValidChange={onValidChange}
        validateAfterSubmit={!!back}>

        <Callout color="warning">
          Tzivos Hashem HQ sends out medals, rank books, Hachayol, and other items for your chayolim approximately once monthly.
          Please indicate whether you would like yours to be shipped to your school or prepared for pickup from our Crown Heights warehouse.
        </Callout>

        <ShippingRow
          required={required}
          onChange={onChange}
          shipping_last={shipping_last}
          shipping_first={shipping_first}
          chidon_first={chidon_first}
          chidon_last={chidon_last}
          shipping_method={shipping_method} />

        <AddressRow
          showPhone
          hideShipping={hideShipping}
          {...restBase}
          title={'School Shipping Address'}
          prefix='shipping_'
          required={required}
          shipping_phone={shipping_phone}
          onChange={onChange} />

        <br />
        {!hideShipping &&
          <Callout color="warning">
            We need to have an alternate residential address for the times that we send out material that will not arrive in your school during school days.
          </Callout>
        }

        <AddressRow
          hideShipping={hideShipping}
          {...restBase}
          title={'Residential Shipping Address'}
          prefix='res_'
          required={required}
          onChange={onChange} />

        {!hideShipping &&
          <Fragment>
            <p className='title'>
              Special Shipping Requests
            </p>
          </Fragment>
        }

        <label>Shipping Notes</label>
        <Input type="textarea" name='shipping_requests' rows='8'
          value={shipping_requests || ''} onChange={onChange} />

        {!back &&
          <SaveButton show={updated} />
        }

        {back &&
          <NavigationRow next back={back} />
        }

      </Form>
    </TabPane>
  )
}
