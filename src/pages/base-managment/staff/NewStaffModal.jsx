import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { FontAwesome } from 'components/ui';
import { PhoneNumber } from 'components/inputs';
import { 
  Modal, ModalHeader, ModalBody, ModalFooter, Button,
  Row, Col, Input, Label, Alert
} from 'reactstrap';
// functions
import { toast } from 'react-toastify';
// import { getParents, createParent } from 'store/parents/operations';

class NewStaffModal extends Component {

  state = {
    father: '', mother: '', last: '', email: '',
    cell: '', home: '', error: false, positions: []
  }

  createParent = ( e ) => {
    e.preventDefault();
    debugger;
  }
  // handle input changes
  onChange = ({ target }) => { this.setState({ [target.id]: target.value }) }

  render(){
    const { isOpen, toggle } = this.props;
    const { error } = this.state;
    // props for all inputs
    const inputProps = { onChange: this.onChange };

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='NewStaffModal'>
        <ModalHeader toggle={ toggle }>Create New Parent</ModalHeader>
        <form onSubmit={ this.createParent }>
          <ModalBody>
            <Row>
              {/* <Col xs={12}>
                <Label>E-Mail Address / Username</Label>
                <Input id='email' value={ email } type='email' {...inputProps} required />
                <div className='invalid-message'>Please enter a valid E-mail address</div>
              </Col>
              <Col xs={6}>
                <Label>Father</Label>
                <Input id='father' value={ father } {...inputProps} pattern='^[a-zA-Z\s.]{2,}$' title="Two or more letters"/>
                <div className='invalid-message'>Please enter 2 or more letters</div>
              </Col>
              <Col xs={6}>
                <Label>Mother</Label>
                <Input id='mother' value={ mother } {...inputProps} pattern='^[a-zA-Z\s.]{2,}$' title="Two or more letters"/>
                <div className='invalid-message'>Please enter 2 or more letters</div>
              </Col>
              <Col xs={12}>
                <Label>Last Name</Label>
                <Input id='last' value={ last } {...inputProps} required pattern='^[a-zA-Z\s.]{3,}$' title="Three or more letters"/>
                <div className='invalid-message'>Please enter 3 or more letters</div>
              </Col>
              <Col xs={6}>
                <Label>Cell Phone</Label>
                <PhoneNumber id='home' value={ home } {...inputProps} required />
                <div className='invalid-message'>Please enter a valid phone number</div>
              </Col>
              <Col xs={6}>
                <Label>Home Phone</Label>
                <PhoneNumber id='cell' value={ cell } {...inputProps} required />
                <div className='invalid-message'>Please enter a valid phone number</div>
              </Col>
              <Col xs={12}>
                <Label>Children</Label>
                <Select options={options} onChange={ this.onSelectChange } isMulti 
                  tabSelectsValue={ false } components={makeAnimated()} />
              </Col> */}
              { error && 
                <div id='errors'>
                  <Alert color='danger'>{ error }</Alert>
                </div>
              }
            </Row>
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
