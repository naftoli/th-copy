import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import { Callout } from 'components/ui';
import { ProfileForm, CCForm } from 'components/functional/payments';
// functions
import { validateCC } from 'functions/validations';
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
    ccInfo: {}
  }

  onProfileSelected = payment_profile_id => {
    this.setState({ payment_profile_id });
  }

  onCCEntered = ccInfo => {
    this.setState({ ccInfo });
  }

  submit = () => {
    const { payment_profile_id, ccInfo } = this.state;
    if ( payment_profile_id ) {
      return this.props.onSubmit({ payment_profile_id });
    }
    // validate the CC before leaving the modal
    validateCC( ccInfo ).then( payment => {
      return this.props.onSubmit( payment );
    }).catch( error => {
      toast.error( error.message );
    })
  }

  render() {
    const { isOpen, centered, toggle } = this.props;
    const { payment_profile_id, ccInfo } = this.state;

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
            <CCForm onChange={ this.onCCEntered } value={ ccInfo } show={ payment_profile_id === false }/>
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
