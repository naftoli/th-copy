import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { FontAwesome } from 'components/ui';
// import { PhoneNumber } from 'components/inputs';
import { 
  Modal, ModalHeader, ModalBody, ModalFooter, Button,
  Row, /* Col, Input, Label, */ Alert
} from 'reactstrap';
// rows
import EditStaffRow from './rows/EditStaffRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
// import { toast } from 'react-toastify';
// import { getParents, createParent } from 'store/parents/operations';

class NewStaffModal extends Component {

  state = {
    staff: {
      username: '', password: '', email: '',
      title: '', first: '', last: '', work: '', cell: '',
    },
    auth: {},
    error: false, 
    positions: []
  }
  // update state.staff
  updateStaff = ({ target }) => {
    this.setState({ staff: { ...this.state.staff, [target.name]: target.value }})
  }
  // update state.auth
  setAuth = auth => this.setState({ auth });
  // onSubmit
  createParent = ( e ) => {
    e.preventDefault();
    debugger;
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

const mapStateToProps = ({ parents }) => ({
  // TODO, return some props
});

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( NewStaffModal );
