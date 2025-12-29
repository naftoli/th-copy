import React from 'react';
import PropTypes from 'prop-types';
// components
import { Radio } from 'components/inputs';
import { Card, CardBody, CardTitle } from 'reactstrap';
// styles
import './PaymentForm.scss';
import CCForm from './CCForm';

export const PaymentForm = ({
  value = {},
  onChange = () => { },
  payments = []
}) => {

  const onProfileSelected = event => {
    let newValue = event.target.value || false;
    // call the onchange function
    return onChange({
      ...value,
      payment_profile_id: newValue
    })
  }

  const onCCEntered = ccValue => {
    onChange(ccValue);
  }

  // show the CC form if....
  const showCCForm = !payments || // 1. payments is fals
    payments.length === 0 || // or it is an array with a length of 0
    value.payment_profile_id === false; // of the selected payment profile is false.

  return (
    <Card className='PaymentForm'>
      <CardBody>
        <CardTitle>
          Payment Method
        </CardTitle>

        {payments && payments.length > 0 &&
          <div className='CCOptions'>
            {payments && payments.map((payment, index) =>
              <CCOption key={index}
                name='payment-profile'
                onChange={onProfileSelected}
                payment={payment.payment.creditCard}
                value={payment.customerPaymentProfileId}
                checked={payment.customerPaymentProfileId.toString() === value.payment_profile_id} />
            )}

            <CCOption name='payment-profile'
              onChange={onProfileSelected}
              checked={value.payment_profile_id === false} />
          </div>
        }

        <CCForm
          value={value}
          show={showCCForm}
          onChange={onCCEntered} />

      </CardBody>
    </Card>
  );
}

// sub-components
const CCOption = ({ onChange, checked, id, name, value, payment }) => {
  // format props for a nice UI
  const inputProps = { onChange, checked, id, name, value };
  const cardNumber = payment ? payment.cardNumber.replace(/\D/g, '') : '';
  const message = cardNumber ? `${payment.cardType} ending in ${cardNumber}` : 'Add New Credit Card';
  const indentifed = payment && payment.cardType ? payment.cardType.toLowerCase() : '';
  // and render radio
  return (
    <div className={`CCOption ${indentifed}`}>
      <Radio {...inputProps}>{message}</Radio>
    </div>
  )
}
