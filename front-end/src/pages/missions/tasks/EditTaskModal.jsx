import React, { useState, useEffect } from 'react';

import EditRow from './includes/EditRow';
import {
  Modal, ModalHeader, ModalFooter,
  ModalBody
} from 'reactstrap';
import { SaveButton } from 'components/buttons';

const initialState = {
  name: '',
  lang: '1',
  label_id: 0,
  short_name: '',
  grid_marking: false,
  mission_marking: false,
  min_level: 0,
  max_level: 0
}

const EditTaskModal = ({ toggle, isOpen, saving, task, updateTask }) => {
  const [state, setState] = useState({ ...initialState });

  useEffect(() => {
    if (isOpen && task) {
      setState(prev => ({
        ...prev,
        ...task
      }));
    }
  }, [isOpen, task]);

  // * Event handlers
  const onCheckboxChange = e => // checkboxes have a different property
    setState(prev => ({ ...prev, [e.target.name]: e.target.checked }));

  const onInputChange = e => // cast input with JSON.parse for cleaner input
    setState(prev => ({ ...prev, [e.target.name]: e.target.value }));

  const onSelectChange = key => option => // handle single select changes
    setState(prev => ({ ...prev, [key]: option ? option.value : false }));

  const onSubmit = e => {
    e.preventDefault();
    updateTask(state);
  }

  return (
    <Modal centered
      toggle={toggle}
      isOpen={isOpen}
      id='EditTaskModal'>

      <ModalHeader toggle={toggle}>
        Update Task
      </ModalHeader>

      <form onSubmit={onSubmit}>
        <ModalBody>

          <EditRow
            {...state}
            onInputChange={onInputChange}
            onSelectChange={onSelectChange}
            onCheckboxChange={onCheckboxChange} />

        </ModalBody>

        <ModalFooter>
          <SaveButton
            saving={saving}
            disabled={saving}>
            Update Task
          </SaveButton>
        </ModalFooter>
      </form>
    </Modal>
  )
}

export default EditTaskModal;
