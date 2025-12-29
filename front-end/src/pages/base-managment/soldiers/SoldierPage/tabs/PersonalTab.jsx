import React, { useState } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Form } from 'components/inputs';
// import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { Row, Col, TabPane, Input } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { DobCol, NameRow, ProfileRow, BasePlatoonRow } from '../../components';

const PersonalTab = ({
  login, soldier, updated, tabId,
  onSubmit, saving, onValidChange,
  handleChange, updateProfile: updateProfileProp
}) => {
  const [cropperModalShow, setCropperModalShow] = useState(false);
  const [uploading, setUploading] = useState(false);

  // handle updates from events (passed from props usually, but here defined locally?)
  // No, handleChange is a prop passed down.
  // The component methods wrap the prop.

  // handle updates from events
  const handleEventChange = (event) => {
    handleChange({ [event.target.id]: event.target.value });
  }

  // when an input changes, update the soldier in the state
  const onInputChange = ({ target }) =>
    handleChange({ [target.id]: target.value });

  // only the dob is changed at the moment
  const onDateChange = date =>
    handleChange({
      dob: date && date.format('YYYY-MM-DD HH:mm:ss')
    });

  // selects do not provide a key by default
  const onSelectChange = key => option =>
    handleChange({ [key]: option ? option.value : option });

  // edit profile
  const toggle = () => {
    setCropperModalShow(prev => !prev);
  }

  // close the modal and update the profile picture
  const updateProfile = (formData) => {
    setUploading(true);
    updateProfileProp(formData)
      .then(() => {
        setUploading(false);
        toggle();
      })
      .catch(() => {
        setUploading(false);
        // Keep modal open on error so user can retry
      });
  }

  let { user_serial, barcode, profilePicture, rank } = soldier;
  // link to the old website if we have a profile picture
  const profile_picture = profilePicture ? `${LEGACY_URL}${profilePicture}` : '';

  return (
    <TabPane tabId={tabId}>
      <Form id='PersonalTab' onSubmit={onSubmit} onValidChange={onValidChange}>

        <Row id='image-row'>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>

            {user_serial && <h4>Serial #: {user_serial}</h4>}
            {barcode && <h4>Barcode: {barcode}</h4>}

            <NameRow soldier={soldier} onChange={handleEventChange} />

          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>

            <ProfileRow
              src={profilePicture}
              rank={rank.rank_ord}
              gender={soldier.gender}
              onImageClick={toggle}
              onGenderChange={onInputChange} />

          </Col>
        </Row>

        <Row>
          <DobCol
            dob={soldier.dob}
            onChange={onDateChange} />

          <Col sm='6' dir='rtl'>
            <label>יום הולדת</label>
            <Input disabled value={soldier.dob_he} />
          </Col>
        </Row>

        <BasePlatoonRow
          isClearable
          code={login.code}
          classId={soldier.class_id}
          schoolId={soldier.school_id}
          onChange={onSelectChange} />

        {/* <AddressRow
          showPhone
          prefix='user_'
          { ...soldier }
          onChange={ this.handleChange } /> */}

        <SaveButton
          show={updated}
          saving={saving}
          disabled={saving} />

      </Form>

      <CropperModal isOpen={cropperModalShow} src={profile_picture}
        toggle={toggle} uploadImage={updateProfile} uploading={uploading} />

    </TabPane>
  );
}

export default PersonalTab;
