import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components

import { Spinner } from 'components/ui';
import { PlatoonSelect, SoldierSelect } from 'components/inputs';
import { Modal, ModalHeader, ModalBody, Row, Col, Label, Collapse } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { isBC } from 'functions/login';
import { getStore } from 'store/rewards/orders/operations';
import { OrderForm } from './OrderForm'

const initialState = {
  class_id: false,  user_id: false,
  loading: false, prize: false, qty: 1
}

class OrderModal extends Component {

  static propTypes = {
    isOpen: PropTypes.bool.isRequired,
    toggle: PropTypes.func.isRequired,
    // onSubmit: PropTypes.func.isRequired
  };
  // clear state on update
  componentDidUpdate({ isOpen }) {
    if ( !isOpen && this.props.isOpen )
      this.setState({ ...initialState });
  }

  // clear the soldier when a platoon is selected
  updatePlatoon = ({ value }) => this.setState({
    class_id: value, user_id: false
  });
  // load the store when a soldier is selected
  updateSoldier = ({ value }) => this.setState(
    { user_id: value },
    () => this.loadStore()
  );
  // update the selected prize
  updatePrize = prize => this.setState({ prize });
  // update the qty
  updateQty = ({ target }) => this.setState({ qty: target.value });

  loadStore = () => {
    this.setState({ loading: true })
    this.props.getStore( this.state.user_id )
    .then( () => this.setState({ loading: false }) )
    .catch( e => {
      toast.error( e.message );
      this.setState({ loading: false, user_id: false })
    });
  }

  state = { ...initialState };

  onSubmit = ( e ) => {
    e.preventDefault();
    debugger;
  }

  render(){
    let { isOpen, login, toggle, store } = this.props;
    const { loading, class_id, user_id, prize, qty } = this.state;
    const bc = isBC( login.code );

    // render the form or a spinner
    let orderForm = <Spinner size={ 5 }/>;

    if ( !loading && store ) orderForm = (
      <OrderForm 
        qty={ qty }
        store={ store }
        prize={ prize }
        updateQty={ this.updateQty }
        updatePrize={ this.updatePrize }/>
    );

    return (
      <Modal isOpen={ isOpen } toggle={ toggle } centered id='OrderModal'>

        <ModalHeader toggle={ toggle }>
          Create Order
        </ModalHeader>

        <form onSubmit={ this.onSubmit }>

          <ModalBody>
            <Row>
              { bc && 
                <Col xs={ 6 }>
                  <Label>Platoon</Label>
                  <PlatoonSelect 
                    value={ class_id }
                    schoolId={ login.id } 
                    onChange={ this.updatePlatoon } />
                </Col>
              }
              
              <Col xs={ bc ? 6 : 12 }>
                <Label>Soldier</Label>
                <SoldierSelect 
                  value={ user_id }
                  classId={ bc ? class_id : login.id }
                  onChange={ this.updateSoldier }/>
              </Col>
            </Row>
            <hr/>

            <Collapse isOpen={ !!user_id }>
              { orderForm }
            </Collapse>
            
          </ModalBody>
        </form>
      </Modal>
    );
  }
}

const mapStateToProps = ({ rewards }) => ({
  store: rewards.orders.store
});

const mapDispatchToProps = {
  getStore
}

export default connect( mapStateToProps, mapDispatchToProps )( OrderModal );
