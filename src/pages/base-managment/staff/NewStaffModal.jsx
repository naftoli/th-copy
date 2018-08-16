import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { FontAwesome } from 'components/ui';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button, Alert } from 'reactstrap';
// rows
import EditStaffRow from './rows/EditStaffRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
import { toast } from 'react-toastify';
import { createStaff, getStaff } from 'store/staff/operations';

const initialState = {
  staff: {
    username: '', password: '', email: '',
    title: '', first: '', last: '', work: '', cell: '',
  },
  auth: {},
  error: false
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
  createParent = ( e ) => {
    e.preventDefault();
    this.setState({ error: false });
    const { staff, auth } = this.state
    this.props.createStaff({ ...staff, auth })
    .then( response => {
      this.props.toggle();  this.props.getStaff();
      this.setState({ ...initialState });
    })
    .catch( ({ message }) => {
      this.setState({ error: message });
      if ( !this.props.isOpen ) toast.error( message );
    })
  }
  // onChange event handler
  onChange = ({ target }) => { this.setState({ [target.id]: target.value }) }

  render(){
    const { isOpen, toggle } = this.props;
    const { error, staff } = this.state;

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='NewStaffModal'>
        <ModalHeader toggle={ toggle }>Create Staff Account</ModalHeader>
        <form onSubmit={ this.createParent }>
          <ModalBody>
            
            <EditStaffRow 
              { ...staff }
              required
              onChange={ this.updateStaff }
              />

            <CreatePositionRow 
              onChange={ this.setAuth }
              />

            { error && 
              <div id='errors'>
                <Alert color='danger'>{ error }</Alert>
              </div>
            }

          </ModalBody>
          <ModalFooter>
            <Button color='primary'>
              <FontAwesome icon='save' /> Create
            </Button>
          </ModalFooter>
        </form>
      </Modal>
    );
  }
}

const mapDispatchToProps = { createStaff, getStaff };

export default connect( null, mapDispatchToProps )( NewStaffModal );
