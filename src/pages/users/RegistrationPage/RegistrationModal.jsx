import React, { Component } from 'react';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import { Callout } from 'components/ui'; 
import PaymentForm from 'components/functional/PaymentForm'
// import { Spinner, FileInput } from 'components/ui';

export class RegistrationModal extends Component {

  static defaultProps = {
    isOpen: false, centered: true,
    toggle: () => {}
  }

  state = { 
    loading: false
  }

  toggleLoading = ( loading ) => {
    this.setState( { loading: loading } )
  }

  render() {
    const { isOpen, centered, toggle } = this.props;
    const { loading } = this.state;

    return (
      <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='RegistrationModal'>
        <ModalHeader toggle={toggle}>Upload Soldier List</ModalHeader>
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
          <PaymentForm />
        </ModalBody>
        { !loading && 
          <ModalFooter>
              <Button color='primary'>Pay and Register</Button>
          </ModalFooter>
        }
      </Modal>
    );
  }
}

export default RegistrationModal;
