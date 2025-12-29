import React, { useState, useEffect } from 'react';
// components
import { SaveButton } from 'components/buttons';
import { BaseSelect } from 'components/inputs';
import {
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col
} from 'reactstrap';
// rows
import { PlatoonRow } from './tabs/PlatoonRow';
import { SettingsRow } from './tabs/SettingsRow';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { onInputChange, onSelectChange, onCheckboxChange, onJSONChange } from 'functions/events';


const initialState = {
  platoon: {
    class_grade: '', class_sub: '', class_teacher: '',
    email: '', cell: '', miles_balance: 1000,
    miles_per_soldier: 100, pic_mission_type: 0, allow_parent_tasks: 0,
    print_parent_tasks: 0, class_gender: null, whatsapp: 1, updated: 1
  },
  saving: false,
}

const NewPlatoonModal = ({ isOpen, login, onSubmit, refresh, toggle: toggleProp }) => {

  const [state, setState] = useState({ ...initialState });

  // Reset state when modal opens/closes
  useEffect(() => {
    if (isOpen) {
      setState({ ...initialState });
    }
  }, [isOpen]);

  const onUpdate = updates =>
    setState(prev => ({ ...prev, platoon: { ...prev.platoon, ...updates } }));

  const onChange = onInputChange(onUpdate);
  const handleSelectChange = onSelectChange(onUpdate);

  const handleJSONChange = onJSONChange(onUpdate);
  const onCheckChange = onCheckboxChange(onUpdate);


  const submit = e => {
    e.preventDefault();
    if (!state.platoon.class_grade)
      return toast.error('Cannot create Platoon without grade.');

    setState(prev => ({ ...prev, saving: true }));

    onSubmit(state.platoon)
      .then(platoon => {
        refresh();
        toggle();
      })
      .catch(e => toast.error(e.message));
  }

  const toggle = () => {
    toggleProp();
    setState({ ...initialState });
  }

  const { platoon, saving } = state;

  const inputProps = { onChange: onChange, required: true };
  const checkProps = { onChange: onCheckChange };

  let baseSelect;
  if (isAdmin(login.code)) {
    baseSelect = (
      <Row>
        <Col xs='12'>
          <label>Base</label>
          <BaseSelect value={state.platoon.school_id}
            onChange={handleSelectChange('school_id')} />
        </Col>
      </Row>
    );
  }

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='NewPlatoonModal'>
      <ModalHeader toggle={toggle}>Create Platoon</ModalHeader>
      <form onSubmit={submit}>
        <ModalBody>

          {baseSelect}

          <PlatoonRow
            platoon={platoon}
            inputProps={inputProps}
            onSelectChange={handleSelectChange}
            checkProps={checkProps}
            onJSONChange={handleJSONChange}
          />

          <p className='title'>Platoon Settings</p>

          <SettingsRow
            modalOnly
            platoon={platoon}
            inputProps={inputProps}
            checkProps={checkProps}
            onJSONChange={handleJSONChange} />

        </ModalBody>

        <ModalFooter>
          <SaveButton show saving={saving} />
        </ModalFooter>
      </form>
    </Modal>
  );
}

export default NewPlatoonModal;
