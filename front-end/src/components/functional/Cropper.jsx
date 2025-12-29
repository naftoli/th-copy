import React, { useRef, useEffect } from 'react';
import { default as CropperJS } from 'cropperjs';
// components
import { Button, ButtonGroup } from 'reactstrap';
import { FontAwesome } from 'components/ui';
// styles
import './styles/Cropper.scss';

export const Cropper = ({
  src = '', cropper: cropperCallback = (cropper) => { }, viewMode = 1, onError
}) => {

  const cropperRef = useRef(null);
  const cropperInstance = useRef(null);

  // setup the cropper instance when the compnent mounts
  useEffect(() => {
    initCropper();
    // Cleanup on unmount
    return () => {
      if (cropperInstance.current) {
        cropperInstance.current.destroy();
      }
    }
  }, []);

  // update cropper when the src changes
  useEffect(() => {
    if (cropperInstance.current) {
      cropperInstance.current.replace(src);
    } else {
      initCropper();
    }
  }, [src]);

  // initialize the cropper library
  const initCropper = () => {
    if (cropperInstance.current) {
      cropperInstance.current.replace(src);
    }
    else if (cropperRef.current) {
      cropperInstance.current = new CropperJS(cropperRef.current, {
        aspectRatio: 1 / 1, // force the square shape we want
        dragMode: 'move',
        viewMode: viewMode, // do not allow the user to add alpha to the image.
        cropBoxMovable: false,
        cropBoxResizable: false,
        checkOrientation: false,
        // autoCropArea: 1,
        ready: () => { cropperInstance.current.zoom(-0.25); }
      });
      // pass the cropper instance to any parents that might want it.
      cropperCallback(cropperInstance.current);
    }
  }

  // this.scale( dir ) returns a function that will always scale in that direction
  const scale = (dir) => () => {
    const cropper = cropperInstance.current;
    if (!cropper) return false; // handle when cropper is not set yet
    const data = cropper.getData();
    // handle scaling if the image is rotated
    if ((data.rotate >= 0 && data.rotate < 90) || (data.rotate >= 180 && data.rotate < 270))
      dir === 'X' ? cropper.scaleX(data.scaleX > 0 ? -1 : 1) : cropper.scaleY(data.scaleY > 0 ? -1 : 1);
    else
      dir === 'X' ? cropper.scaleY(data.scaleY > 0 ? -1 : 1) : cropper.scaleX(data.scaleX > 0 ? -1 : 1);
  }

  const editing = {
    rotateLeft: () => { cropperInstance.current && cropperInstance.current.rotate(-90); },
    rotateRight: () => { cropperInstance.current && cropperInstance.current.rotate(90); },
    zoomIn: () => { cropperInstance.current && cropperInstance.current.zoom(0.05); },
    zoomOut: () => { cropperInstance.current && cropperInstance.current.zoom(-0.05); },
    scaleX: scale('X'),
    scaleY: scale('Y')
  };

  return (
    <div style={{ maxWidth: '100%', borderRadius: '5px' }}>
      <div style={{ maxHeight: '60vh' }}>
        <img
          style={{ maxHeight: '60vh' }}
          src={src}
          alt="cropper-img"
          ref={cropperRef}
          onError={onError} />
      </div>
      <CropperControls {...editing} />
    </div>
  );
}

// component to hold all the buttons
const CropperControls = (props) => {
  return (
    <ButtonGroup id='cropper-controls'>
      <Button color='primary' onClick={props.rotateLeft}><FontAwesome icon='undo' /></Button>
      <Button color='primary' onClick={props.rotateRight}><FontAwesome icon='redo' /></Button>
      <Button color='primary' onClick={props.zoomIn}><FontAwesome icon='search-plus' /></Button>
      <Button color='primary' onClick={props.zoomOut}><FontAwesome icon='search-minus' /></Button>
      <Button color='primary' onClick={props.scaleX}><FontAwesome icon='arrows-alt-h' /></Button>
      <Button color='primary' onClick={props.scaleY}><FontAwesome icon='arrows-alt-v' /></Button>
    </ButtonGroup>
  );
};

export default Cropper;