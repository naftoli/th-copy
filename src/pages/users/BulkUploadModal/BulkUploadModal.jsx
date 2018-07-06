import React, { Component } from 'react';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import { Callout } from '@blueprintjs/core';
import FileInput from 'components/ui/FileInput';

export class BulkUploadModal extends Component {

  static defaultProps = {
    isOpen: false, centered: true,
    toggle: () => {}
  }

  upload = () => {

  }

  render() {
    const { isOpen, centered, toggle } = this.props;

    return (
      <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='cropper-modal'>
        <ModalHeader toggle={toggle}>School or Class Upload</ModalHeader>
        <ModalBody>
          <Callout intent="primary" title="Directions:">
            <ol style={{ paddingLeft: '0px' }}>
              <li>Download the <a href="//mashpia.com/students.xls">spreadsheet</a> (Excel/.xls)</li>
              <li>Enter all information into spreadsheet.<br/>
                <strong>
                  Please Note: You MUST have the First Name, Last Name, 
                  First Name Hebrew, Last name Hebrew, English Date of Birth, Gender, 
                  and Mission Type fields of each student filled out.
                </strong>
              </li>
              <li>Upload spreadsheet into system using the file input below.</li>
            </ol>
          </Callout>
          <FileInput />
        </ModalBody>
        <ModalFooter>
          <Button color="primary" onClick={ this.upload }>Upload</Button>
        </ModalFooter>
      </Modal>
    );
  }
}

export default BulkUploadModal;
