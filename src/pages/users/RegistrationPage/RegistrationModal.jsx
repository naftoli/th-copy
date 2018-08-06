import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import { Callout } from 'components/ui';
import { ProfileForm, CCForm } from 'components/functional/payments';
// import { Spinner, FileInput } from 'components/ui';
// functions
import Payment from 'payment';
import { toast } from 'react-toastify';

export class RegistrationModal extends Component {

  static propTypes = {
    onSubmit: PropTypes.func.isRequired
  }

  static defaultProps = {
    isOpen: false, centered: true,
    toggle: () => {}
  }

  state = { 
    payment_profile_id: undefined,
    cc_info: {}
  }

  onProfileSelected = payment_profile_id => {
    this.setState({ payment_profile_id });
  }

  onCCEntered = cc_info => {
    this.setState({ cc_info });
  }

  validateCC = () => {
    const { number, expiry, cvc } = this.state.cc_info;
    return new Promise( ( resolve, reject ) => {
      if ( !Payment.fns.validateCardNumber( number ) ) {
        reject( new Error('Invalid Credit Card Number') );
      } else if ( !expiry ) {
        reject( new Error('Invalid Exparation Date') );
      } else if ( !cvc ) {
        reject( new Error('Invalid CVC (Code)') );
      }
      const mapped_cc = { 'cc-number': number, 'cc-exp': expiry, 'x_card_code': cvc };
      resolve( mapped_cc );
    });
  }

  submit = () => {
    const { payment_profile_id } = this.state;
    if ( payment_profile_id ) {
      return this.props.onSubmit({ payment_profile_id });
    }
    // validate the CC before leaving the modal
    this.validateCC().then( payment => {
      return this.props.onSubmit( payment );
    }).catch( error => {
      toast.error( error.message );
    })
  }

  render() {
    const { isOpen, centered, toggle } = this.props;
    const { payment_profile_id } = this.state;

    return (
      <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='RegistrationModal'>
        <ModalHeader toggle={toggle}>Registration Payment</ModalHeader>
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
          <ProfileForm onProfileSelected={this.onProfileSelected} value={payment_profile_id}>
            <CCForm onInputChange={ this.onCCEntered } show={ payment_profile_id === false }/>
          </ProfileForm>
        </ModalBody>
        <ModalFooter>
          <Button color='primary' onClick={this.submit}>
            Pay and Register
          </Button>
        </ModalFooter>
      </Modal>
    );
  }
}

export default RegistrationModal;
