import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
import {
  Modal, ModalHeader, ModalBody,
  ModalFooter, Row, Col, Input
} from 'reactstrap';
import { SaveButton } from 'components/buttons/index';
import { MissionTypeSelect } from 'components/selects';
import { Select } from 'components/inputs';
import {
  NameRow, DobCol, ProfileRow, BasePlatoonRow
} from '../components';
import { toast } from 'react-toastify';
// data
import { allMissionLanguages } from 'data/languages.json'

const initialSoldier = {
  first: '', last: '', first_he: '',
  last_he: '', gender: 'M', admin_id: 0,
  admin_email: ''
}

export const NewSoldierModal = ({
  toggle: toggleProp, login, isOpen, image, editPicture, onSubmit: onSubmitProp
}) => {

  const [saving, setSaving] = useState(false);
  const [soldier, setSoldier] = useState({ ...initialSoldier });

  const setupSoldier = () => {
    const { code, school_id, class_id } = login;
    if (code === 'BC')
      return setSoldier({ ...initialSoldier, school_id });
    if (code === 'TEACHER')
      return setSoldier({ ...initialSoldier, school_id, class_id });
    // reset the soldier by default
    return setSoldier({ ...initialSoldier });
  }

  // Effect to handle modal open/close updates or initial mount
  // However, in functional components 'componentDidMount' equivalent is useEffect([], ...).
  // But here we might want to reset when isOpen changes to TRUE? 
  // The original had:
  // componentDidMount -> setupSoldier
  // toggle -> toggleProp() AND setupSoldier()
  // So basically whenever it was toggled (closed/opened) it reset.
  useEffect(() => {
    if (isOpen) {
      setupSoldier();
    }
  }, [isOpen]);

  const toggle = () => {
    toggleProp();
    // setupSoldier(); // This might be redundant if we rely on useEffect, but safe to keep logic consistent?
    // Actually, if we toggle CLOSED, we don't necessarily need to reset immediately, 
    // but if we toggle OPEN it should reset.
    // The previous code reset on toggle call.
    setupSoldier();
  };

  const updateSoldier = updates => setSoldier(prev => ({ ...prev, ...updates }));

  const onInputChange = ({ target }) =>
    updateSoldier({ [target.id]: target.value });

  const onDateChange = date =>
    updateSoldier({
      dob: date && date.format('YYYY-MM-DD HH:mm:ss')
    });

  const onSelectChange = key => option =>
    updateSoldier({ [key]: option.value });

  const onSubmit = e => {
    e.preventDefault();

    const { mobile_pic } = image;

    const soldierData = { ...soldier, mobile_pic };
    setSaving(true);

    Promise.resolve(onSubmitProp(soldierData))
      .then(() => {
        setSaving(false);
        setSoldier({ ...initialSoldier });
      },
        err => {
          toast.error(err.error);
          setSaving(false);
        });
  }

  return (
    <Modal centered
      isOpen={isOpen}
      id='NewSoldierModal'
      toggle={toggle}>

      <ModalHeader toggle={toggle}>
        Create Soldier
      </ModalHeader>

      <form onSubmit={onSubmit}>
        <ModalBody>

          <ProfileRow
            src={image.profilePicture}
            gender={soldier.gender}
            onImageClick={editPicture}
            onGenderChange={onInputChange} />

          <NameRow
            inst={login.inst_id}
            soldier={soldier}
            onChange={onInputChange} />

          <Row>
            <DobCol
              dob={soldier.dob}
              onChange={onDateChange} />

            <Col sm='6'>
              <label htmlFor='mission_type'>Mission Type</label>
              <MissionTypeSelect
                required id='mission_type'
                gender={soldier.gender}
                value={soldier.school_type_id}
                onChange={onSelectChange('school_type_id')} />
            </Col>
          </Row>

          <Row>
            <Col sm='6'>
              <label htmlFor='mission_lang'>Mission Language</label>
              <Select
                required id='mission_lang'
                options={allMissionLanguages}
                selected={soldier.lang_id}
                onChange={onSelectChange('lang_id')} />
            </Col>
          </Row>

          <BasePlatoonRow
            required
            code={login.code}
            classId={soldier.class_id}
            schoolId={soldier.school_id}
            onChange={onSelectChange} />

          <Row>
            <Col sm='6'>
              <label htmlFor='admin_id'>Parent Admin ID</label>
              <Input
                type='number' name='admin_id' className='form-control'
                id='admin_id' onChange={onInputChange} />
            </Col>
            <Col sm='6'>
              <label htmlFor='admin_email'>Parent Email</label>
              <Input
                type='email' name='admin_email' className='form-control'
                id='admin_email' onChange={onInputChange} />
            </Col>
          </Row>

        </ModalBody>

        <ModalFooter>
          <SaveButton show saving={saving} />
        </ModalFooter>
      </form>
    </Modal>
  )
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect(
  mapStateToProps, // mapDispatchToProps
)(NewSoldierModal)
