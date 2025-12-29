import React, { useState } from 'react';

import OptionsRow from './includes/OptionsRow';
import {
  Modal, ModalHeader, ModalFooter,
  ModalBody
} from 'reactstrap';
import { SaveButton } from 'components/buttons';

const initialState = {
  lang: '1',
  task: '',
  grades: [],
  parsha_ids: [],
  short_name: '',
  school_id: false,
  subject_id: false,
  grid_marking: false,
  school_type_id: 0,
  mission_marking: true,
}

const AddTaskModal = ({ toggle, isOpen, saving, login, createTask }) => {
  const [state, setState] = useState({ ...initialState });

  // * Event handlers
  const onCheckboxChange = e => // checkboxes have a different property
    setState(prev => ({ ...prev, [e.target.name]: e.target.checked }));

  const onInputChange = e => // cast input with JSON.parse for cleaner input
    setState(prev => ({ ...prev, [e.target.name]: e.target.value }));

  const onSelectChange = key => option => // handle single select changes
    setState(prev => ({ ...prev, [key]: option ? option.value : false }));

  const onMultiSelectChange = key => options => // handle multi select changes
    setState(prev => ({ ...prev, [key]: options ? options.map(opt => opt.value) : false }));

  // * reset the modal
  const clearState = () =>
    setState({ ...initialState });

  const onSubmit = e => {
    e.preventDefault();
    if (!saving)
      createTask(state)
        .then(clearState);
  }

  return (
    <Modal centered
      toggle={toggle}
      isOpen={isOpen}
      id='AddTaskModal'>

      <ModalHeader toggle={toggle}>
        Add Task
      </ModalHeader>

      <form onSubmit={onSubmit}>
        <ModalBody>

          <OptionsRow
            login={login}
            {...state}
            onInputChange={onInputChange}
            onSelectChange={onSelectChange}
            onCheckboxChange={onCheckboxChange}
            onMultiSelectChange={onMultiSelectChange} />

        </ModalBody>

        <ModalFooter>
          <SaveButton
            saving={saving}
            disabled={saving}>
            Create Task
          </SaveButton>
        </ModalFooter>
      </form>
    </Modal>
  )
}

export default AddTaskModal;
