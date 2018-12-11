import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Modal, ModalHeader, ModalBody, ModalFooter, Alert, Collapse } from 'reactstrap';
// rows
import EditStaffRow from './rows/EditStaffRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { SaveButton } from 'components/buttons';
import { createStaff, getStaff } from 'store/base/staff/operations';

const initialState = {
  staff: {
    username: '', password: '', email: '',
    title: '', first: '', last: '', work: '', cell: '',
  },
  auth: {},
  error: false,
  saving: false
}

class NewStaffModal extends Component {

  state = { ...initialState }
  // update state.staff
  updateStaff = ({ target }) => {
    this.setState({ staff: { ...this.state.staff, [target.name]: target.value }})
  }
  // update state.auth
  setAuth = auth => this.setState({ auth });
  // onSubmit
  createStaff = ( e ) => {
    e.preventDefault();

    this.setState({ error: false, saving: true });
    const { staff, auth } = this.state;

    this.props.createStaff({ ...staff, auth })
    .then( () => {
      this.props.toggle();
      this.props.getStaff();
      this.setState({ ...initialState });
    })
    .catch( ({ message }) => {
      this.setState({ error: message });
      if ( !this.props.isOpen ) toast.error( message );
    })
    .then( () => this.setState({ saving: false }) );

  }

  render(){
    const { isOpen, toggle, login } = this.props;
    const { error, staff, saving } = this.state;

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='NewStaffModal'>
        
        <ModalHeader toggle={ toggle }>
          Create Staff Account
        </ModalHeader>
        
        <form onSubmit={ this.createStaff }>
          <ModalBody>
            <EditStaffRow
              required
              { ...staff }
              onChange={ this.updateStaff } />

            <CreatePositionRow 
              login={ login }
              onChange={ this.setAuth }
              isAdmin={ isAdmin( login.code ) } />

            <Collapse isOpen={ !!error }>
              <div id='errors'>
                <Alert color='danger'>{ error }</Alert>
              </div>
            </Collapse>
          </ModalBody>

          <ModalFooter>
            <SaveButton text='Create' saving={ saving } />
          </ModalFooter>
        </form>
      </Modal>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

const mapDispatchToProps = { createStaff, getStaff };

export default connect( mapStateToProps, mapDispatchToProps )( NewStaffModal );
