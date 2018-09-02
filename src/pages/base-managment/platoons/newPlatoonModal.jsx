import React, { Component } from 'react';
// components
import { SaveButton } from 'components/buttons';
import { BaseSelect } from 'components/inputs';
import { 
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col
} from 'reactstrap';
// rows
import PlatoonRow from './rows/PlatoonRow';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';

const initialState = {
  platoon: {
    class_grade: '', class_sub: '', class_teacher: '',
    email: '', cell: '', miles_balance: 1000,
    miles_per_soldier: 200
  },
  saving: false,
}

class NewPlatoonModal extends Component {

  state = { ...initialState }
  // update state.staff
  onChange = ({ target }) => {
    this.setState({ platoon: { ...this.state.platoon, [target.name]: target.value } });
  }
  onSelectChange = ( option ) => {
    this.setState({ platoon: { ...this.state.platoon, [option.id]: option.value } });
  };
  onBaseChange = ( option ) => {
    this.onSelectChange({ id: 'school_id', ...option })
  };

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
    const selectProps = { onChange: this.onSelectChange };

    let baseSelect;
    if ( isAdmin( login.code ) ) {
      baseSelect = (
        <Row>
          <Col xs='12'>
            <label>Base</label>
            <BaseSelect value={ this.state.platoon.school_id } 
              onChange={ this.onBaseChange } />
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
            selectProps={ selectProps } />

          <pre>{ JSON.stringify( platoon, null, 2 ) }</pre>

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
