import React, { Component } from 'react';
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
    print_parent_tasks: 0, class_gender: null, whatsapp: 1
  },
  saving: false,
}

class NewPlatoonModal extends Component {

  state = { ...initialState }
  
  onUpdate = updates =>
    this.setState({ platoon: { ...this.state.platoon, ...updates }});

  onChange = onInputChange( this.onUpdate );
  onSelectChange = onSelectChange( this.onUpdate );

  onJSONChange = onJSONChange( this.onUpdate );
  onCheckChange = onCheckboxChange( this.onUpdate );


  submit = e => {
    e.preventDefault();
    if ( !this.state.platoon.class_grade )
      return toast.error( 'Cannot create Platoon without grade.' );

    this.setState({ saving: true });

    this.props.onSubmit( this.state.platoon )
    .then( platoon => { 
      this.props.refresh();
      this.toggle();
    })
    .catch( e => toast.error( e.message ) );
  }

  toggle = () => {
    this.props.toggle();
    this.setState({ ...initialState });
  }

  render(){
    const { isOpen, login } = this.props;
    const { platoon, saving } = this.state;

    const inputProps = { onChange: this.onChange, required: true };
    const checkProps = { onChange: this.onCheckChange };

    let baseSelect;
    if ( isAdmin( login.code ) ) {
      baseSelect = (
        <Row>
          <Col xs='12'>
            <label>Base</label>
            <BaseSelect value={ this.state.platoon.school_id } 
              onChange={ this.onSelectChange('school_id') } />
          </Col>
        </Row>
      );
    }

    return (
      <Modal isOpen={ isOpen } toggle={ this.toggle } centered id='NewPlatoonModal'>
        <ModalHeader toggle={ this.toggle }>Create Platoon</ModalHeader>
        <form onSubmit={ this.submit }>
          <ModalBody>
          
          { baseSelect }

          <PlatoonRow 
            platoon={ platoon } 
            inputProps={ inputProps } 
            onSelectChange={ this.onSelectChange } 
            checkProps={ checkProps }
            onJSONChange={ this.onJSONChange }
            />

          <p className='title'>Platoon Settings</p>

          <SettingsRow
            modalOnly
            platoon={ platoon } 
            inputProps={ inputProps }
            checkProps={ checkProps }
            onJSONChange={ this.onJSONChange } />            

          </ModalBody>

          <ModalFooter>
            <SaveButton show saving={ saving } />
          </ModalFooter>
        </form>
      </Modal>
    );
  }
}

export default NewPlatoonModal;
