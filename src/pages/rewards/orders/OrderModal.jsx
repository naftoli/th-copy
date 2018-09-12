import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { SaveButton } from 'components/buttons';
import { Select } from 'components/inputs';
import { 
  Modal, ModalHeader, ModalBody, ModalFooter,
  Row, Col, Label, Input
} from 'reactstrap';
// functions
import { filterUpdates } from 'functions/events';
import { Spinner } from 'components/ui/loading/index';

class OrderModal extends Component {

  // static propTypes = {
  //   isOpen: PropTypes.bool.isRequired,
  //   toggle: PropTypes.func.isRequired,
  //   onSubmit: PropTypes.func.isRequired
  // };

  // state = {};

  componentDidUpdate({ isOpen }) {
    // if ( !isOpen && this.props.isOpen )
    //   this.setState({ updates: {} });
  }

  onSubmit = ( e ) => {
    e.preventDefault();
    debugger;
  }

  render(){
    let { isOpen, login, toggle } = this.props;

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='OrderModal'>

        <ModalHeader toggle={ toggle }>
          Create Order
        </ModalHeader>

        <form onSubmit={ this.onSubmit }>

          <ModalBody>
            <Row>
              <Col xs={ 6 }>
                <Label>Platoon</Label>
                <Select />
              </Col>

              <Col xs={ 6 }>
                <Label>Soldier</Label>
                <Select />
              </Col>
            </Row>
            <Row id='total-row'>
              {/* <Spinner size={ 5 }/> */}
              <Col xs={ 8 }>
                <Label>Prize</Label>
                <Select />
              </Col>

              <Col xs={ 4 }>
                <Label>Qty</Label>
                <Input type='number' min={ 1 } max={ 100 }/>
                <div className='invalid-message'>Must be 1 - 100</div>
              </Col>

              <Col xs={ 4 }>
                <Label>Soldier's Miles</Label>
                <p>1,014</p>
              </Col>

              <Col xs={ 4 }>
                <Label>Total Price</Label>
                <p>500</p>
              </Col>

              <Col xs={4}>
                <SaveButton 
                  saving={ false }
                  text='Place Order'
                  show={ true } />
              </Col>
            </Row>
          </ModalBody>
        </form>
      </Modal>
    );
  }
}

export default OrderModal;
