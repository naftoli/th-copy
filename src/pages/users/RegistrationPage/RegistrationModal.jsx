import React, { Component } from 'react';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import { Callout } from 'components/ui'; 
// import { Spinner, FileInput } from 'components/ui';

export class RegistrationModal extends Component {

  static defaultProps = {
    isOpen: false, centered: true,
    toggle: () => {}
  }

  state = { loading: false }

  toggleLoading = ( loading ) => {
    this.setState( { loading: loading } )
  }

  render() {
    const { isOpen, centered, toggle } = this.props;
    const { loading } = this.state;

    return (
      <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='registration-modal'>
        <ModalHeader toggle={toggle}>Upload Soldier List</ModalHeader>
        <ModalBody>

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
