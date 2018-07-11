import React, { Component } from 'react';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import { Callout } from '@blueprintjs/core';
import FileInput from 'components/ui/FileInput';

export class BulkUploadModal extends Component {

  static defaultProps = {
    isOpen: false, centered: true,
    toggle: () => {}
  }
  inputRef = React.createRef();

  upload = () => {
    const file = this.inputRef.current.files[0];
    if ( !file ) return this.props.upload( false );
    
    const formData = new FormData();
    formData.append( 'users', file, file.name );
    this.props.upload( formData );
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
            <p>
              <strong>Please understand that all soldiers in your spreadsheet will be added to the system.
                <em> We do not, and will not, prevent you from creating duplicate accounts.</em></strong>
            </p>
          </Callout>
          <FileInput inputRef={ this.inputRef }/>
        </ModalBody>
        <ModalFooter>
          <Button color="primary" onClick={ this.upload }>Upload</Button>
        </ModalFooter>
      </Modal>
    );
  }
}

export default BulkUploadModal;
