import React, { Component } from 'react';
import { default as CropperJS } from 'cropperjs';
// components
import { Button, ButtonGroup } from 'reactstrap';
import { FontAwesome } from 'components/ui';
// styles
import './styles/Cropper.scss';

export class Cropper extends Component {
  // the props we are expecting
  static defaultProps = { 
    src: '', cropper: ( cropper ) => {} 
  }
  // setup some internal props on creation
  constructor( props ) {
    super( props );
    this.cropperRef = React.createRef();
    this.cropper = false;
  }

  // setup the cropper instance when the compnent mounts
  componentDidMount() {
    this.setCropper();
  }
  // update cropper when the src changes
  componentDidUpdate( prevProps) {
    if ( prevProps.src !== this.props.src )
      this.setCropper();
  }

  // initialize the cropper library
  setCropper() {
    // remove the existing cropper instance
    if ( this.cropper ) { 
      this.cropper.replace( this.props.src ); 
    }
    // and create a new one
    else if ( this.cropperRef.current ){
      this.cropper = new CropperJS( this.cropperRef.current, {
          aspectRatio: 1 / 1, // force the square shape we want
          dragMode: 'move', viewMode: 1, // do not allow the user to add alpha to the image.
          cropBoxMovable: false, cropBoxResizable: false
      });
    }
    // pass the cropper instance to any parents that might want it.
    this.props.cropper( this.cropper );
  }
  // object with all the editing features of cropper that we are using
  editing = () => {
    return {
      rotateLeft:   () => { this.cropper && this.cropper.rotate( -90 ); },
      rotateRight:  () => { this.cropper && this.cropper.rotate(  90 ); },
      zoomIn:   () => { this.cropper && this.cropper.zoom(  0.1 ); },
      zoomOut:  () => { this.cropper && this.cropper.zoom( -0.1 ); },
      scaleX: this.scale('X'),
      scaleY: this.scale('Y')
    }
  }
  // this.scale( dir ) returns a function that will always scale in that direction
  scale = ( dir ) => () => {
    const cropper = this.cropper;
    if ( !cropper ) return false; // handle when cropper is not set yet
    const data = cropper.getData();
    // handle scaling if the image is rotated
    if ( ( data.rotate >= 0 && data.rotate < 90 ) || ( data.rotate >= 180 && data.rotate < 270 ) )
      dir === 'X' ? cropper.scaleX( data.scaleX > 0 ? -1 : 1 ) : cropper.scaleY( data.scaleY > 0 ? -1 : 1 );
    else
      dir === 'X' ? cropper.scaleY( data.scaleY > 0 ? -1 : 1 ) : cropper.scaleX( data.scaleX > 0 ? -1 : 1 );
  }

  render() {
    const { src } = this.props;
    return (
      <div style={{ maxWidth: '100%', borderRadius: '5px' }}>
        <img src={ src } alt="cropper-img" ref={ this.cropperRef }/>
        <CropperControls { ...this.editing() } />
      </div>
    );
  }
}

// component to hold all the buttons
const CropperControls = ( props ) => {
  return (
    <ButtonGroup id='cropper-controls'>
      <Button color='primary' onClick={ props.rotateLeft }><FontAwesome icon='undo'/></Button>
      <Button color='primary' onClick={ props.rotateRight }><FontAwesome icon='redo'/></Button>
      <Button color='primary' onClick={ props.zoomIn }><FontAwesome icon='search-plus'/></Button>
      <Button color='primary' onClick={ props.zoomOut }><FontAwesome icon='search-minus'/></Button>
      <Button color='primary' onClick={ props.scaleX }><FontAwesome icon='arrows-alt-h'/></Button>
      <Button color='primary' onClick={ props.scaleY }><FontAwesome icon='arrows-alt-v'/></Button>
    </ButtonGroup>
  );
};

export default Cropper;