import React, { useState } from 'react';
import PropTypes from 'prop-types';
// components
import { Callout } from 'components/ui';
import { PaymentForm } from 'components/functional/payments';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
// functions
import { validateCC } from 'functions/validations';
import { showError } from 'functions/notifications';

export const RegistrationModal = ({
  payments = [],
  onSubmit,
  isOpen = false,
  centered = true,
  toggle = () => { }
}) => {

  const [payment, setPayment] = useState({});

  const onChange = newPayment => {
    setPayment(newPayment);
  }

  const submit = () => {
    const { payment_profile_id, ...paymentData } = payment;
    // if they selected a payment profile, then just submit it
    if (payment_profile_id) {
      return onSubmit({ payment_profile_id });
    }
    // validate the CC before leaving the modal
    showError(
      validateCC(paymentData)
        .then(validPayment => onSubmit(validPayment))
    );
  }

  return (
    <Modal
      isOpen={isOpen}
      toggle={toggle}
      centered={centered}
      id='RegistrationModal'>

      <ModalHeader toggle={toggle}>
        Registration Payment
      </ModalHeader>

      <ModalBody>
        <Callout title='Refund Policy'>
          <p>
            <strong>Registration:</strong> We will not refund any legitimate registration even if the program was not used on your end.
          </p>
          <p>
            <strong>Processing errors:</strong> For any overcharge of registration due to technical errors we will fully refund.
            Credit card transactions will be credited to the original card used. This process may take up to two weeks.
          </p>
        </Callout>

        <PaymentForm
          value={payment}
          payments={payments}
          onChange={onChange} />
      </ModalBody>

      <ModalFooter>
        <Button color='primary' onClick={submit}>
          Pay and Register
        </Button>
      </ModalFooter>
    </Modal>
  );
}

RegistrationModal.propTypes = {
  payments: PropTypes.array,
  onSubmit: PropTypes.func.isRequired,
}

export default RegistrationModal;
