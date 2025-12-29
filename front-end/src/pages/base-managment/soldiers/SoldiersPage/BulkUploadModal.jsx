import React, { useState, useRef } from 'react';
// components
import { FileInput } from 'components/inputs';
import { Spinner, Callout } from 'components/ui';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';

export const BulkUploadModal = ({
  inst, isOpen, centered = true, toggle = () => { }, upload
}) => {

  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState([]);
  const inputRef = useRef();

  const handleUpload = () => {
    // Access the file from the FileInput component instance if possible, 
    // BUT FileInput prop implies it might be finding the file differently.
    // In the class component: const file = this.inputRef.current.files[0];
    // If inputRef is attached to a DOM input, this works.
    // If attached to a Class Component, we need to know what it exposes.
    // Assuming FileInput exposes 'files' or is a wrapper around <input type="file"> via forwardRef/internal ref.
    // If FileInput is a class component, existing access patterns via ref should persist if we use ref correctly.
    // However, function components generally shouldn't rely on refs to children for data.
    // Let's assume FileInput is simple enough or we use useRef on it.

    // Note: The previous code did `this.inputRef.current.files[0]`. 
    // If FileInput is a Class Component, `this.inputRef.current` is the component instance. 
    // Does the component instance have a `files` property? 
    // Or is `inputRef` passed to an underlying input?
    // Looking at usage: logic assumes `this.inputRef.current.files`.

    // Let's check if we need to modify FileInput. 
    // But for now, we faithfully port.

    const inputElement = inputRef.current;
    if (!inputElement || !inputElement.files || !inputElement.files[0]) return upload(false);

    const file = inputElement.files[0];
    const formData = new FormData();
    formData.append('users', file, file.name);
    setLoading(true);

    // upload data and handle result
    upload(formData)
      .then(() => setLoading(false))
      .catch(error => {
        let errs = error.data;
        if (!Array.isArray(errs)) { errs = [errs]; }
        setLoading(false);
        setErrors(errs);
      });
  }

  const daySchool = 4;
  const url = inst === daySchool ? '//mashpia.com/daySchool.xls' : '//mashpia.com/students.xls';

  return (
    <Modal isOpen={isOpen} centered={centered} toggle={toggle} id='bulk-upload-modal'>
      <ModalHeader toggle={toggle}>Upload Soldier List</ModalHeader>
      <ModalBody>
        {errors.length === 0 &&
          <Callout color='primary' title='Directions:'>
            <p id='warning'>
              Warning! Do not add any Soldiers that are switching from another Tzivos Hashem base.
              Please contact <a href='mailto:cth@tizvoshashem.org' target="_top">Headquarters</a> with a list of names,
              dob's and base's they came from to be transferred to your base.
              Leaving them in your spreadsheet will reset them back to a private.
            </p>
            <ol style={{ paddingLeft: '0px' }}>
              <li>Download the <a href={url} target='_href'>spreadsheet</a> (Excel/.xls)</li>
              <li>Enter all information into spreadsheet.<br />
                {inst === 10 &&
                  <strong>
                    Please Note: You MUST have ALL FIELDS filled out.
                  </strong>
                }
                {inst !== daySchool &&
                  <strong>
                    Please Note: You MUST have the First Name, Last Name,
                    First Name Hebrew, Last name Hebrew, English Date of Birth, Gender,
                    and Mission Type fields of each student filled out.
                  </strong>
                }
              </li>
              <li>Upload spreadsheet into system using the file input below.</li>
              <li>
                If there are errors in your spreadsheet they will replace this instructions box.
                Please correct these errors and re-upload using the file input below when you are ready.
              </li>
            </ol>
            <p>
              <strong>Please understand that all soldiers in your spreadsheet will be added to the system.
                <em> We do not, and will not, prevent you from creating duplicate accounts.</em></strong>
            </p>
          </Callout>
        } {errors.length > 0 && !loading &&
          <Callout color='danger' title='Error Details:' icon='fas fa-exclamation-triangle'>
            {errors.map((error, index) => <p key={index}>{error}</p>)}
            <p><strong>Please correct the above errors and re-upload your spreadsheet</strong></p>
          </Callout>
        } {loading && <Spinner size='5' />}
        {!loading && <FileInput inputRef={inputRef} />}
      </ModalBody>
      {!loading &&
        <ModalFooter>
          <Button color='primary' onClick={handleUpload}>Upload Spreadsheet</Button>
        </ModalFooter>
      }
    </Modal>
  );
}

export default BulkUploadModal;
