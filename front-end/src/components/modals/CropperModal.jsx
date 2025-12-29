import React, { useState, useRef, useEffect } from 'react';
import { DEFAULT_PROFILE, DEFAULT_LOGO, DEFAULT_PRIZE } from 'components/constants';
// components
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';
import Cropper from 'components/functional/Cropper';
import { FontAwesome } from 'components/ui';
// functions
import is from 'is_js';
import { toast } from 'react-toastify';
import { readFile } from 'functions/utils';

const CropperModal = ({
  isOpen = true, centered = true, src: propsSrc = false, fileName = 'profile',
  uploadImage: uploadImageProp = (formData, contentType) => { },
  uploading = false, toggle, viewMode: viewModeProp = 1
}) => {

  const uploadRef = useRef(null);
  const cropperRef = useRef(null); // To store the cropper instance

  const [src, setSrc] = useState(propsSrc);
  const [name, setName] = useState('default');
  const [viewMode] = useState(viewModeProp); // viewMode doesn't seem to change in original, but initialized from props

  const setCropper = (cropper) => { cropperRef.current = cropper; }

  // open the OS/Browsers file select dialog
  const openImage = () => {
    // clear any previously selected files so that they can select the same file again on desktop
    if (uploadRef.current) {
      uploadRef.current.value = '';
      uploadRef.current.click();
    }
  }

  // Read the image file selected by the user and update the state
  const readImageFile = () => {
    const files = uploadRef.current.files;
    setName(files[0].name || 'unknown');
    // read the file if we can
    if (files && files.length) {
      readFile(files[0]).then(fr => setSrc(fr.result));
    }
  }

  // create the formData and call the uploadImage function with the data that it needs
  const uploadImage = () => {
    if (!cropperRef.current) return false;
    try {
      cropperRef.current.getCroppedCanvas({ width: 500, height: 500 }).toBlob(blob => {
        const formData = new FormData();
        formData.append(fileName, blob, name);
        // API must be called with 'application/x-www-form-urlencoded; charset=utf-8' for img to post
        uploadImageProp(formData);
      });
    } catch (e) {
      console.error(e);
      toast.error(
        `There was an error while cropping your image. ` +
        `Please select a different image and/or use a different browser`
      );
    }
  }

  // update the image if we where passed a new one
  useEffect(() => {
    // update the image if we get a new prop
    if (propsSrc !== src) {
      setSrc(propsSrc);
    }
  }, [propsSrc]);

  useEffect(() => {
    if (!isOpen && propsSrc !== src) {
      setSrc(propsSrc);
    }
  }, [isOpen]);


  const handleError = e => {
    setSrc(false);
  }

  let displaySrc = src;
  displaySrc = displaySrc && displaySrc.indexOf(DEFAULT_PROFILE) >= 0 ? false : displaySrc;
  displaySrc = displaySrc && displaySrc.indexOf(DEFAULT_LOGO) >= 0 ? false : displaySrc;
  displaySrc = displaySrc && displaySrc.indexOf(DEFAULT_PRIZE) >= 0 ? false : displaySrc;

  // assume we do not have an image
  let body =
    <div style={{ textAlign: 'center' }}>
      <Button color="primary" size="lg" outline onClick={openImage} disabled={uploading}>
        <FontAwesome icon='camera' /> Select Image
      </Button>
    </div>;

  // if we do, render the cropper component
  if (displaySrc)
    body = <Cropper src={displaySrc}
      onError={handleError}
      cropper={setCropper}
      viewMode={viewMode} />;

  // render the final modal
  return (
    <Modal isOpen={isOpen} id='cropper-modal'
      centered={centered} toggle={uploading ? undefined : (is.not.mobile() ? toggle : undefined)}>
      <ModalHeader toggle={uploading ? undefined : toggle}>
        {uploading ? 'Uploading...' : 'Edit / Upload Image'}
      </ModalHeader>
      <ModalBody>
        <input type="file" style={{ display: 'none' }} ref={uploadRef} onChange={readImageFile} disabled={uploading} />
        {body}
      </ModalBody>
      {displaySrc &&
        <ModalFooter>
          <Button color="primary" onClick={openImage} disabled={uploading}>
            Change Image
          </Button>
          <Button color="primary" onClick={uploadImage} disabled={uploading}>
            {uploading ? (
              <span style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <FontAwesome icon='spinner' spin /> Uploading...
              </span>
            ) : 'Save / Replace'}
          </Button>
        </ModalFooter>
      }
    </Modal>
  );
}

export default CropperModal;
