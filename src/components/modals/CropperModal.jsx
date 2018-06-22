import React, { Component } from 'react';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button, ButtonGroup } from 'reactstrap';
import Cropper from 'cropperjs';
import './styles/CropperModal.scss';
import 'cropperjs/dist/cropper.css';

const CropperControls = ( props ) => (
  <ButtonGroup id='cropper-controls'>
    <Button color="primary" onClick={props.rotateLeft}><i className="fas fa-undo"/></Button>
    <Button color="primary" onClick={props.rotateRight}><i className="fas fa-redo"/></Button>
    <Button color="primary" onClick={props.zoomIn}><i className="fas fa-search-plus"/></Button>
    <Button color="primary" onClick={props.zoomOut}><i className="fas fa-search-minus"/></Button>
    <Button color="primary" onClick={props.scaleX}><i className="fas fa-arrows-alt-h"/></Button>
    <Button color="primary" onClick={props.scaleY}><i className="fas fa-arrows-alt-v"/></Button>
  </ButtonGroup>
);

class CropperModal extends Component {

  static defaultProps = {
    isOpen: true, centered: true, src: false,
    uploadImage: () => {}
  }

  constructor( props ) {
    super( props );
    // refs to manipulate the HTML
    this.cropperRef = React.createRef();  this.uploadRef = React.createRef();
    this.cropper = false;
    // store the src in state so it can be changed
    this.state = { src: props.src };
  }

  setCropper() {
    if ( this.cropperRef.current ){
      // remove the existing cropper instance
      if ( this.cropper ) { this.cropper.destroy() }
      // and create a new one
      this.cropper = new Cropper( this.cropperRef.current, {
          aspectRatio: 1 / 1,
          dragMode: 'move'
      });
    }
  }

  openImage = () => {
    this.uploadRef.current.value = ''; // clear any previously selected files
    this.uploadRef.current.click();
  }

  readImageFile = () => {
    const files = this.uploadRef.current.files;
    if ( FileReader && files && files.length ) {
      const fr = new FileReader();
      fr.onload = () => {
        this.setState({ src: fr.result });
      }
      fr.readAsDataURL( files[0] );
    }
  }

  uploadImage = () => {
    if ( !this.cropper ) return false;

    this.cropper.getCroppedCanvas({ maxWidth: 500, maxHeight: 500 }).toBlob( blob => {
      const formData = new FormData();
      formData.append( 'profile', blob, this.uploadRef.current.files[0].name );
      // API must be called with 'application/x-www-form-urlencoded; charset=utf-8' for img to post
      this.props.uploadImage( formData );
    });
  }
  // cropper editing functions
  rotateLeft = () => { this.cropper.rotate( -90 ); }
  rotateRight = () => { this.cropper.rotate( 90 ); }
  zoomIn = () => { this.cropper.zoom( 0.1 ); }
  zoomOut = () => { this.cropper.zoom( -0.1 ); }
  scaleX = () => {
    const data = this.cropper.getData();
    if ( ( data.rotate >= 0 && data.rotate < 90 ) || ( data.rotate >= 180 && data.rotate < 270 ) ){
      this.cropper.scaleX( data.scaleX > 0 ? -1 : 1 );
    } else {
      this.cropper.scaleY( data.scaleY > 0 ? -1 : 1 );
    }
  }
  scaleY = () => {
    const data = this.cropper.getData();
    if ( ( data.rotate >= 0 && data.rotate < 90 ) || ( data.rotate >= 180 && data.rotate < 270 ) ){
      this.cropper.scaleY( data.scaleY > 0 ? -1 : 1 ); 
    } else {
      this.cropper.scaleX( data.scaleX > 0 ? -1 : 1 );
    }
  }

  componentDidMount(){
    this.setCropper();
  }

  componentDidUpdate() {
    this.setCropper();
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
    if ( src ) {
      body =
        <div style={{ maxWidth: '100%', borderRadius: '5px' }}>
          <img src={ src } alt="cropper-img" ref={ this.cropperRef }/>
          <CropperControls rotateRight={this.rotateRight} rotateLeft={this.rotateLeft}
            zoomIn={this.zoomIn} zoomOut={this.zoomOut} scaleX={this.scaleX} scaleY={this.scaleY}/>
        </div>;
    }
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