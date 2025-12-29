import React from 'react';
import { Modal, ModalBody, ModalFooter, Button } from 'reactstrap';

const styles = {
  body: { textAlign: 'center', fontSize: '2em' }
}

const ConfirmationModal = ({
  isOpen, message, callback
}) => {

  const accept = () => {
    callback(true);
  }

  const cancel = () => {
    callback(false);
  }

  return (
    <Modal isOpen={isOpen} toggle={cancel} centered={true}>
      <ModalBody><p style={styles.body}>{message}</p></ModalBody>
      <ModalFooter>
        <Button color='primary' onClick={accept}>Yes</Button>{' '}
        <Button color='danger' onClick={cancel}>No</Button>
      </ModalFooter>
    </Modal>
  );
}

export default ConfirmationModal;