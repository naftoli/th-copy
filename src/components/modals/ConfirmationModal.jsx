import React, { Component } from 'react';
import { Modal, ModalBody, ModalFooter, Button } from 'reactstrap';

const styles = {
  body: { textAlign: 'center', fontSize: '2em' }
}

class ConfirmationModal extends Component {

  accept = () => {
    this.props.callback( true );
  }

  cancel = () => {
    this.props.callback( false );
  }

  render(){
    const { isOpen, message } = this.props;
    return (
      <Modal isOpen={isOpen} onClick={ this.cancel } centered={true}>
        <ModalBody><p style={ styles.body }>{ message }</p></ModalBody>
        <ModalFooter>
          <Button color='primary' onClick={this.accept}>Yes</Button>{' '}
          <Button color='danger' onClick={this.cancel}>No</Button>
        </ModalFooter>
      </Modal>
    );
  }
}

export default ConfirmationModal;