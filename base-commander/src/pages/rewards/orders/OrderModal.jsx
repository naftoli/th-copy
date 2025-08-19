import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import { Spinner } from 'components/ui';
import { OrderForm } from './OrderForm';
import { PlatoonSelect, SoldierSelect } from 'components/inputs';
import { Modal, ModalHeader, ModalBody, Row, Col, Label, Collapse } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { isBC } from 'functions/login';
import { getStore, placeOrder } from 'store/rewards/orders/operations';

const initialState = {
  class_id: false,  user_id: false,
  loading: false, items: [],
  saving: false
}

class OrderModal extends Component {

  static propTypes = {
    isOpen: PropTypes.bool.isRequired,
    toggle: PropTypes.func.isRequired,
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
  // update an item at specific index
  updateItem = (index, updatedItem) => {
    this.setState(prev => {
      const newItems = prev.items.map((item, i) => i === index ? updatedItem : item);
      
      // Calculate total cost
      const total = newItems.reduce((sum, item) => {
        return sum + (item.prize && item.qty ? item.prize.points * item.qty : 0);
      }, 0);
      
      // If total exceeds available miles, adjust quantities or show error
      if (total > this.props.store.miles) {
        // Try to adjust the quantity of the current item
        if (updatedItem.prize && updatedItem.qty) {
          const maxQty = Math.floor(this.props.store.miles / updatedItem.prize.points);
          if (maxQty > 0) {
            // Adjust quantity to fit within available miles
            const adjustedItem = { ...updatedItem, qty: Math.min(updatedItem.qty, maxQty) };
            const adjustedItems = newItems.map((item, i) => i === index ? adjustedItem : item);
            
            // Show toast notification
            toast.warning(`Quantity adjusted to ${adjustedItem.qty} due to insufficient miles.`);
            
            return { items: adjustedItems };
          } else {
            // Remove the item if it can't be afforded at all
            toast.error('Item removed - insufficient miles for this prize.');
            return { items: newItems.filter((_, i) => i !== index) };
          }
        }
      }
      
      return { items: newItems };
    });
  };
  
  // add a new item
  addItem = () => {
    this.setState(prev => {
      const newItems = [...prev.items, { prize: false, qty: 1 }];
      
      // If this is the first item and we have store data, set the first available prize
      if (newItems.length === 1 && this.props.store && this.props.store.prizes && this.props.store.prizes.length > 0) {
        newItems[0].prize = this.props.store.prizes[0];
      }
      
      // Check if adding this item would exceed available miles
      const total = newItems.reduce((sum, item) => {
        return sum + (item.prize && item.qty ? item.prize.points * item.qty : 0);
      }, 0);
      
      if (total > this.props.store.miles) {
        toast.error('Cannot add more items - insufficient miles available.');
        return prev; // Don't add the item
      }
      
      return { items: newItems };
    });
  };
  
  // remove an item at specific index
  removeItem = (index) => {
    this.setState(prev => ({
      items: prev.items.filter((_, i) => i !== index)
    }));
  };

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
    this.setState({ saving: true })

    // Convert items array to the format expected by the API
    const orderData = {
      ...this.state,
      items: this.state.items.map(item => ({
        prize_id: item.prize.prize_id,
        qty: item.qty
      }))
    };

    this.props.placeOrder( orderData )
    .then( this.props.toggle )
    .catch( e => toast.error( e.message ) )
    .then( () => this.setState({ saving: false }) )
  }

  render(){
    let { isOpen, login, toggle, store } = this.props;
    const { loading, class_id, user_id, items, saving } = this.state;
    const bc = isBC( login.code );

    // render the form or a spinner
    let orderForm = <Spinner size={ 5 }/>;

    if ( !loading && store ) orderForm = (
      <OrderForm 
        items={ items }
        store={ store }
        saving={ saving }
        updateItem={ this.updateItem }
        addItem={ this.addItem }
        removeItem={ this.removeItem }/>
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
                  schoolId={ login.school_id }
                  classId={ class_id || login.class_id }
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

const mapStateToProps = ({ rewards, login }) => ({
  login: login.current_login,
  store: rewards.orders.store
});

const mapDispatchToProps = {
  getStore, placeOrder
}

export default connect( mapStateToProps, mapDispatchToProps )( OrderModal );
