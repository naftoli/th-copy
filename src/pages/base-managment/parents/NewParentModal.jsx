import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { SaveButton } from 'components/buttons';
import { Select, PhoneNumber } from 'components/inputs';
import { 
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col, Input, Label, Alert
} from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { getChildOptions } from './misc/functions';
import { onInputChange, onMultiSelectChange } from 'functions/events';
import { getParents, createParent } from 'store/base/parents/operations';

const initialState = {
  father: '', mother: '', last: '',   email: '',
  cell: '',   home: '',   children: [],
  error: false, saving: false
}

class NewParentModal extends Component {

  state = { ...initialState };

  onUpdate = update =>
    this.setState( update );

  createParent = ( e ) => {
    e.preventDefault();
    const { father, mother, children } = this.state;
    // make sure the server can make a first name
    if ( father === '' && mother === '' )
      return this.setState({ error: `Please enter either the father or mother's name`});
    // make sure they have children
    if ( children.length === 0 )
      return this.setState({ error: `Please select some soldiers.`});

    this.setState({ saving: true });

    this.props.createParent( this.state )
    .then( () => {
      this.props.toggle();
      this.props.getParents();
      this.setState({ ...initialState });
    })
    .catch( error => {
      this.setState({ error: error.message });
      // otherwise show the error message to the user as a notification
      if ( !this.props.isOpen ) toast.error( error.message );
    })
    .then( () => this.setState({ saving: false }) );
  }
  // handle input changes
  onChange = onInputChange( this.onUpdate );
  // handle when they select children
  onSelectChange = onMultiSelectChange( this.onUpdate );

  render(){
    const { isOpen, toggle } = this.props;
    const { error, saving, father, mother, last, cell, home, email } = this.state;
    // props for all inputs
    const inputProps = { onChange: this.onChange };
    const options = getChildOptions( this.props.availableChildren );

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='NewParentModal'>
        <ModalHeader toggle={ toggle }>Create New Parent</ModalHeader>
        <form onSubmit={ this.createParent }>
          <ModalBody>
            <Row>
              <Col xs={12}>
                <Label>E-Mail Address / Username</Label>
                <Input name='email' value={ email } type='email' {...inputProps} required />
                <div className='invalid-message'>Please enter a valid E-mail address</div>
              </Col>
              
              <Col xs={6}>
                <Label>Father</Label>
                <Input name='father' value={ father } {...inputProps} pattern='^[a-zA-Z\s.]{2,}$' title="Two or more letters"/>
                <div className='invalid-message'>Please enter 2 or more letters</div>
              </Col>

              <Col xs={6}>
                <Label>Mother</Label>
                <Input name='mother' value={ mother } {...inputProps} pattern='^[a-zA-Z\s.]{2,}$' title="Two or more letters"/>
                <div className='invalid-message'>Please enter 2 or more letters</div>
              </Col>

              <Col xs={12}>
                <Label>Last Name</Label>
                <Input name='last' value={ last } {...inputProps} required pattern='^[a-zA-Z\s.]{3,}$' title="Three or more letters"/>
                <div className='invalid-message'>Please enter 3 or more letters</div>
              </Col>

              <Col xs={6}>
                <Label>Cell Phone</Label>
                <PhoneNumber name='home' value={ home } {...inputProps} required />
                <div className='invalid-message'>Please enter a valid phone number</div>
              </Col>

              <Col xs={6}>
                <Label>Home Phone</Label>
                <PhoneNumber name='cell' value={ cell } {...inputProps} required />
                <div className='invalid-message'>Please enter a valid phone number</div>
              </Col>

              <Col xs={12}>
                <Label>Children</Label>
                <Select isMulti
                  options={ options } tabSelectsValue={ false }
                  onChange={ this.onSelectChange('children') } />
              </Col>
              { error && 
                <div id='errors'>
                  <Alert color='danger'>{ error }</Alert>
                </div>
              }
            </Row>
          </ModalBody>
          <ModalFooter>
            <SaveButton text='Create' saving={ saving } />
          </ModalFooter>
        </form>
      </Modal>
    );
  }
}

const mapStateToProps = ({ base }) => ({
  availableChildren: base.parents.children,
});

const mapDispatchToProps = { getParents, createParent };

export default connect( mapStateToProps, mapDispatchToProps )( NewParentModal );
