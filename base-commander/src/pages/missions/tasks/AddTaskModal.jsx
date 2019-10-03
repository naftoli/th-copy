import React, { Component } from 'react';

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

class AddTaskModal extends Component {

  state = {
    ...initialState
  }

  // * Event handlers
  onCheckboxChange = e => // checkboxes have a different property
    this.setState( { [e.target.name]: e.target.checked } );

  onInputChange = e => // cast input with JSON.parse for cleaner input
    this.setState( { [e.target.name]: e.target.value } );

  onSelectChange = key => option => // handle single select changes
    this.setState({ [ key ]: option ? option.value : false });

  onMultiSelectChange = key => options => // handle multi select changes
    this.setState({ [ key ]: options ? options.map( opt => opt.value ) : false });

  // * reset the modal
  clearState= () =>
    this.setState({ ...initialState });

  onSubmit = e => {
    e.preventDefault();
    if ( !this.props.saving )
      this.props.createTask( this.state )
      .then( this.clearState );
  }

  render() {
    const { toggle, isOpen, saving, login } = this.props;

    return (
      <Modal centered
          toggle={ toggle }
          isOpen={ isOpen }
          id='AddTaskModal'>

        <ModalHeader toggle={ toggle }>
          Add Task
        </ModalHeader>

        <form onSubmit={ this.onSubmit }>
          <ModalBody>

            <OptionsRow
              login={ login }
              { ...this.state }
              onInputChange={ this.onInputChange }
              onSelectChange={ this.onSelectChange }
              onCheckboxChange={ this.onCheckboxChange }
              onMultiSelectChange={ this.onMultiSelectChange } />

          </ModalBody>

          <ModalFooter>
            <SaveButton
                saving={ saving }
                disabled={ saving }>
              Create Task
            </SaveButton>
          </ModalFooter>
        </form>
      </Modal>
    )
  }
}

export default AddTaskModal;
