import React, { Component } from 'react';

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
}

class EditTaskModal extends Component {

  state = {
    ...initialState
  }

  componentDidUpdate( { isOpen } ) {
    if ( !isOpen && this.props.isOpen )
      this.setState({ ...this.props.task });
  }

  // * Event handlers
  onCheckboxChange = e => // checkboxes have a different property
    this.setState( { [e.target.name]: e.target.checked } );

  onInputChange = e => // cast input with JSON.parse for cleaner input
    this.setState( { [e.target.name]: e.target.value } );

  onSelectChange = key => option => // handle single select changes
    this.setState({ [ key ]: option ? option.value : false });

  onSubmit = e => {
    e.preventDefault();
    this.props.updateTask( this.state );
  }

  render() {
    const { toggle, isOpen, saving } = this.props;

    return (
      <Modal centered
          toggle={ toggle }
          isOpen={ isOpen }
          id='EditTaskModal'>

        <ModalHeader toggle={ toggle }>
          Update Task
        </ModalHeader>

        <form onSubmit={ this.onSubmit }>
          <ModalBody>

            <EditRow
              { ...this.state }
              onInputChange={ this.onInputChange }
              onSelectChange={ this.onSelectChange }
              onCheckboxChange={ this.onCheckboxChange } />

          </ModalBody>

          <ModalFooter>
            <SaveButton
                saving={ saving }
                disabled={ saving }>
              Update Task
            </SaveButton>
          </ModalFooter>
        </form>
      </Modal>
    )
  }
}

export default EditTaskModal;
