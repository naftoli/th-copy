import React, { Component } from 'react';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import Cropper from 'components/functional/Cropper';
import { toast } from 'react-toastify';

class CropperModal extends Component {
  // the props we are expecting
  static defaultProps = {
    isOpen: true, centered: true, src: false,
    uploadImage: ( formData, contentType ) => {}
  }

  constructor( props ) {
    super( props );
    // internal variables
    this.uploadRef = React.createRef(); this.cropper = false;
    // initial state
    this.state = { 
      src: props.src, 
      name: 'default' 
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
    if ( FileReader && files && files.length ) {
      const fr = new FileReader();
      fr.onload = () => { this.setState({ src: fr.result }); }
      fr.readAsDataURL( files[0] );
    }
  }

  // create the formData and call the uploadImage function with the data that it needs
  uploadImage = () => {
    if ( !this.cropper ) return false;
    try {
      this.cropper.getCroppedCanvas({ width: 500, height: 500 }).toBlob( blob => {
        const formData = new FormData();
        formData.append( 'profile', blob, this.state.name );
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

  render() {
    // extract the props and state
    const { isOpen, centered, toggle } = this.props;
    const { src } = this.state;
    // assume we do not have an image
    let body = 
      <div style={{textAlign: 'center'}}>
        <Button color="primary" size="lg" outline onClick={ this.openImage }>
          <i className="fas fa-camera"></i> Select Image
        </Button>
      </div>;
    // if we do, render the cropper component
    if ( src ) 
      body = <Cropper src={ src } cropper={ this.setCropper } />;
    // render the final modal
    return (
      <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='cropper-modal'>
        <ModalHeader toggle={toggle}>Edit / Upload Image</ModalHeader>
        <ModalBody>
          <input type="file" style={{display: 'none'}} ref={ this.uploadRef } onChange={ this.readImageFile }/>
          { body }
        </ModalBody>
        <ModalFooter>
          <Button color="primary" onClick={ this.openImage }>Replace</Button>
          <Button color="primary" onClick={ this.uploadImage }>Upload</Button>
        </ModalFooter>
      </Modal>
    );
  }
}

export default CropperModal;