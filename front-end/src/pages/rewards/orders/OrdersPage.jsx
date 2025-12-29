import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Button } from 'reactstrap';
import OrderModal from './OrderModal';
import { Navigate } from 'react-router-dom';
import { ButtonBar, Callout, SelectTable, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { getColumns } from './include/columns';
import { isBC, isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
// state
import { getOrders, processOrders } from 'store/rewards/orders/operations';
// styles
import './include/orders.scss';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const OrdersPage = () => {
  const dispatch = useDispatch();
  const { orders, loading, login } = useSelector(({ rewards, login }) => ({
    ...rewards.orders,
    login: login.current_login
  }));

  const [modal, setModal] = useState(false);
  const [selection, setSelection] = useState([]);
  const [redeemed, setRedeemed] = useState(false);

  const loadOrders = () => {
    dispatch(getOrders(redeemed))
      .then(() => setSelection([]))
      .catch(e => toast.error(e.message));
  }

  useEffect(() => {
    setTitle('Store Orders');
    loadOrders();
  }, [redeemed]); // Re-load when redeemed status changes

  // toggle modal
  const toggleModal = () => setModal(prev => !prev);

  // table helpers
  const getId = row => row.user_prize_id;
  const toggleRow = (newSelection, row) => setSelection(newSelection);
  const toggleAll = (newSelection) => setSelection(newSelection);

  const toggleOrderStatus = () => {
    setRedeemed(prev => !prev);
  };

  const processOrdersHandler = action => () => {
    if (action === 'delete'
      && !window.confirm('Are you sure you want to delete and refund these orders? This action cannot be undone.')
    ) return false;

    const order_count = selection.length;
    const toast_id = createNotifcation(`Updating ${order_count} orders.`);

    dispatch(processOrders(action, selection))
      .then(() => updateNotifcation(toast_id, `${order_count} orders updated!`))
      .then(loadOrders)
      .catch(e => updateNotifcation(toast_id, '', e.message, false));
  }

  const toCSV = () => {
    const headers = [
      'Date', 'First Name', 'Last Name', 'Serial Number', 'Prize',
      'Miles', 'Qty', 'Total', 'Grade', 'Sub'
    ];
    const rows = orders.map(order => [
      order.created, order.first, order.last, order.user_serial,
      order.prize_name, order.points, order.quantity, order.total * -1,
      order.class_grade, order.class_sub
    ]);
    return dataToCSV(headers, rows);
  }

  // non-admins go to their prizes
  if (isAdmin(login.code))
    return <Navigate to='/rewards/templates' replace />;

  console.log(orders)

  let columns = getColumns(isBC(login.code, true));

  return (
    <div id='OrdersPage' className='full-height'>
      <Callout title='Store Orders'>
        <p>Create and manage orders coming in from Teachers, Kiosks and Parent Accounts</p>
        <strong>This page loads open orders. </strong>
        To see old orders, please press the "Load Redeemed Orders" button.
      </Callout>

      <ButtonBar>
        <Button className='btn btn-primary' onClick={toggleModal}>
          <FontAwesome icon='plus' /> Create Order
        </Button>

        <Button color='primary' onClick={loadOrders}>
          <InlineSync loading={loading} />{' '}
          {loading ? 'Loading...' : 'Refresh'}
        </Button>

        <Button color='primary' onClick={toggleOrderStatus}>
          <FontAwesome icon={redeemed ? 'store' : 'archive'} />{' '}
          Load {redeemed ? 'Open' : 'Redeemed'} Orders
        </Button>

        {canDownload(orders) &&
          <CSVLink
            data={toCSV()}
            filename={"store_orders.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Orders (CSV/Excel)
            </Button>
          </CSVLink>
        }

        {redeemed &&
          <Button color='primary'
            onClick={processOrdersHandler('unredeem')}
            disabled={selection.length === 0}>
            <FontAwesome icon='undo' /> Un-redeem
          </Button>
        }

        {!redeemed &&
          <Button color='primary'
            onClick={processOrdersHandler('redeem')}
            disabled={selection.length === 0}>
            <FontAwesome icon='box' /> Redeem
          </Button>
        }

        <Button color='primary'
          onClick={processOrdersHandler('delete')}
          disabled={selection.length === 0}>
          <FontAwesome icon='trash' /> Delete
        </Button>

      </ButtonBar>

      <SelectTable
        data={orders}
        getId={getId}
        pageId='OrdersPage'
        columns={columns}
        loading={loading}
        selection={selection}
        toggleRow={toggleRow}
        toggleAll={toggleAll}
        maxSelectionSize={orders.length} />

      <OrderModal
        login={login}
        isOpen={modal}
        toggle={toggleModal} />

    </div>
  );
}

export default OrdersPage;
