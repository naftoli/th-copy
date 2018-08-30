import React, { Component } from 'react';
import { DEFAULT_PROFILE, DEFAULT_LOGO } from 'components/constants';
// components
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import Cropper from 'components/functional/Cropper';
import { FontAwesome } from 'components/ui';
// functions
import is from 'is_js';
import { toast } from 'react-toastify';
import { readFile } from 'functions/utils';

class CropperModal extends Component {
  // the props we are expecting
  static defaultProps = {
    isOpen: true, centered: true, src: false, fileName: 'profile',
    uploadImage: ( formData, contentType ) => {}
  }

  constructor( props ) {
    super( props );
    // internal variables
    this.uploadRef = React.createRef(); this.cropper = false;
    // initial state
    this.state = { 
      src: props.src, 
      name: 'default',
      viewMode: 1
    };
  }

  setCropper = ( cropper ) => { this.cropper = cropper; }
  
  // open the OS/Browsers file select dialog
  openImage = () => {
    // clear any previously selected files so that they can select the same file again on desktop
    this.uploadRef.current.value = '';
    this.uploadRef.current.click();
  }

  // Read the image file selected by the user and update the state
  readImageFile = () => {
    const files = this.uploadRef.current.files;
    this.setState({ name: files[0].name || 'unknown' });
    // read the file if we can
    if ( files && files.length ) {
      readFile( files[0] ).then( fr => this.setState({ src: fr.result }) );
    }
  }

  // create the formData and call the uploadImage function with the data that it needs
  uploadImage = () => {
    if ( !this.cropper ) return false;
    try {
      this.cropper.getCroppedCanvas({ width: 500, height: 500 }).toBlob( blob => {
        const formData = new FormData();
        formData.append( this.props.fileName, blob, this.state.name );
        // API must be called with 'application/x-www-form-urlencoded; charset=utf-8' for img to post
        this.props.uploadImage( formData );
      });
    } catch ( e ) {
      console.error( e );
      toast.error( 
        `There was an error while cropping your image. ` + 
        `Please select a different image and/or use a different browser`
      );
    }
  }

  // update the image if we where passed a new one
  componentDidUpdate( prevProps ) {
    // update the image if we get a new prop
    if ( this.props.src !== prevProps.src ) {
      this.setState({ src: this.props.src });
    // clear any images that we replaced the existing one with
    } else if ( !this.props.isOpen && this.props.src !== this.state.src ) {
      this.setState({ src: this.props.src });
    }
  }

  handleError = e => {
    this.setState({ src: false });
  }

  render() {
    // extract the props and state
    const { isOpen, centered, toggle, viewMode } = this.props;
    let { src } = this.state;
    src = src && src.indexOf( DEFAULT_PROFILE ) >= 0 ? false : src;
    src = src && src.indexOf( DEFAULT_LOGO ) >= 0 ? false : src;
    // assume we do not have an image
    let body = 
      <div style={{textAlign: 'center'}}>
        <Button color="primary" size="lg" outline onClick={ this.openImage }>
          <FontAwesome icon='camera' /> Select Image
        </Button>
      </div>;
    // if we do, render the cropper component
    if ( src ) 
      body = <Cropper src={ src } onError={ this.handleError } cropper={ this.setCropper } viewMode={ viewMode }/>;
    // render the final modal
    return (
      <Modal isOpen={isOpen} id='cropper-modal' zIndex='auto'
          centered={centered} toggle={ is.not.mobile() ? toggle : undefined }>
        <ModalHeader toggle={toggle}>Edit / Upload Image</ModalHeader>
        <ModalBody>
          <input type="file" style={{display: 'none'}} ref={ this.uploadRef } onChange={ this.readImageFile }/>
          { body }
        </ModalBody>
        <ModalFooter>
          <Button color="primary" onClick={ this.openImage }>Change Image</Button>
          <Button color="primary" onClick={ this.uploadImage }>Save / Replace</Button>
        </ModalFooter>
      </Modal>
    );
  }
}

export default CropperModal;