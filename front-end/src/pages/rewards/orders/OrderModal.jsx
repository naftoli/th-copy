import React, { useState, useEffect } from 'react';
import { connect, useDispatch } from 'react-redux';
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
  class_id: false, user_id: false,
  loading: false, items: [],
  saving: false
}

const OrderModal = ({ isOpen, toggle, login, store }) => {
  const dispatch = useDispatch();
  const [state, setState] = useState(initialState);
  const { class_id, user_id, loading, items, saving } = state;

  // clear state on update
  useEffect(() => {
    if (!isOpen) {
      setState(initialState);
    }
  }, [isOpen]);

  // clear the soldier when a platoon is selected
  const updatePlatoon = ({ value }) => setState(prev => ({
    ...prev, class_id: value, user_id: false
  }));

  const loadStore = (userId) => {
    setState(prev => ({ ...prev, loading: true }));
    dispatch(getStore(userId))
      .then(() => setState(prev => ({ ...prev, loading: false })))
      .catch(e => {
        toast.error(e.message);
        setState(prev => ({ ...prev, loading: false, user_id: false }));
      });
  }

  // load the store when a soldier is selected
  const updateSoldier = ({ value }) => {
    setState(prev => ({ ...prev, user_id: value }));
    loadStore(value);
  }

  // update an item at specific index
  const updateItem = (index, updatedItem) => {
    setState(prev => {
      const newItems = prev.items.map((item, i) => i === index ? updatedItem : item);

      // Calculate total cost
      const total = newItems.reduce((sum, item) => {
        return sum + (item.prize && item.qty ? item.prize.points * item.qty : 0);
      }, 0);

      // If total exceeds available miles, adjust quantities or show error
      if (total > store.miles) {
        // Try to adjust the quantity of the current item
        if (updatedItem.prize && updatedItem.qty) {
          const maxQty = Math.floor(store.miles / updatedItem.prize.points);
          if (maxQty > 0) {
            // Adjust quantity to fit within available miles
            const adjustedItem = { ...updatedItem, qty: Math.min(updatedItem.qty, maxQty) };
            const adjustedItems = newItems.map((item, i) => i === index ? adjustedItem : item);

            // Show toast notification
            toast.warning(`Quantity adjusted to ${adjustedItem.qty} due to insufficient miles.`);

            return { ...prev, items: adjustedItems };
          } else {
            // Remove the item if it can't be afforded at all
            toast.error('Item removed - insufficient miles for this prize.');
            return { ...prev, items: newItems.filter((_, i) => i !== index) };
          }
        }
      }

      return { ...prev, items: newItems };
    });
  };

  // add a new item
  const addItem = () => {
    setState(prev => {
      const newItems = [...prev.items, { prize: false, qty: 1 }];

      // If this is the first item and we have store data, set the first available prize
      if (newItems.length === 1 && store && store.prizes && store.prizes.length > 0) {
        newItems[0].prize = store.prizes[0];
      }

      // Check if adding this item would exceed available miles
      const total = newItems.reduce((sum, item) => {
        return sum + (item.prize && item.qty ? item.prize.points * item.qty : 0);
      }, 0);

      if (total > store.miles) {
        toast.error('Cannot add more items - insufficient miles available.');
        return prev; // Don't add the item
      }

      return { ...prev, items: newItems };
    });
  };

  // remove an item at specific index
  const removeItem = (index) => {
    setState(prev => ({
      ...prev,
      items: prev.items.filter((_, i) => i !== index)
    }));
  };

  const onSubmit = (e) => {
    e.preventDefault();
    setState(prev => ({ ...prev, saving: true }));

    // Convert items array to the format expected by the API
    const orderData = {
      ...state,
      items: state.items.map(item => ({
        prize_id: item.prize.prize_id,
        qty: item.qty
      }))
    };

    dispatch(placeOrder(orderData))
      .then(toggle)
      .catch(e => toast.error(e.message))
      .then(() => setState(prev => ({ ...prev, saving: false })));
  }

  const bc = isBC(login.code);

  // render the form or a spinner
  let orderForm = <Spinner size={5} />;

  if (!loading && store) orderForm = (
    <OrderForm
      items={items}
      store={store}
      saving={saving}
      updateItem={updateItem}
      addItem={addItem}
      removeItem={removeItem} />
  );

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='OrderModal'>

      <ModalHeader toggle={toggle}>
        Create Order
      </ModalHeader>

      <form onSubmit={onSubmit}>

        <ModalBody>
          <Row>
            {bc &&
              <Col xs={6}>
                <Label>Platoon</Label>
                <PlatoonSelect
                  value={class_id}
                  schoolId={login.id}
                  onChange={updatePlatoon} />
              </Col>
            }

            <Col xs={bc ? 6 : 12}>
              <Label>Soldier</Label>
              <SoldierSelect
                value={user_id}
                schoolId={login.school_id}
                classId={class_id || login.class_id}
                onChange={updateSoldier} />
            </Col>
          </Row>
          <hr />

          <Collapse isOpen={!!user_id}>
            {orderForm}
          </Collapse>

        </ModalBody>
      </form>
    </Modal>
  );
}

OrderModal.propTypes = {
  isOpen: PropTypes.bool.isRequired,
  toggle: PropTypes.func.isRequired,
  login: PropTypes.object.isRequired,
  store: PropTypes.object
};

const mapStateToProps = ({ rewards, login }) => ({
  login: login.current_login,
  store: rewards.orders.store
});

export default connect(mapStateToProps, null)(OrderModal);
