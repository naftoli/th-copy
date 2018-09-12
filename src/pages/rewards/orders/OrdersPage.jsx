import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button, ButtonGroup } from 'reactstrap';
import OrderModal from './OrderModal';
import { Callout, SelectTable, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isBC } from 'functions/login';
import { getColumns } from './include/columns';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
// state
import { getOrders, processOrders } from 'store/rewards/orders/operations';
// styles
import './include/orders.scss';

class OrdersPage extends Component {

  state = { 
    modal: false,
    selection: [],
    redeemed: false,
  };

  componentDidMount() { 
    setTitle( 'Store Orders' );
    this.loadOrders();
  }

  // refs
  checkboxTable = React.createRef();
  checkAll = null;

  // modal
  toggleModal = () => this.setState({ modal: !this.state.modal });

  // table
  getId = row => row.user_prize_id;
  toggleRow = ( selection, row ) => this.setState({ selection });
  toggleAll = ( selection ) => this.setState({ selection });

  // Network
  loadOrders = () => {
    this.props.getOrders( this.state.redeemed )
    .then( this.setState({ selection: [] }))
    .catch( e => toast.error( e.message ) );
  }
  toggleOrderStatus = () => {
    this.setState(
      { redeemed: !this.state.redeemed },
      () => this.loadOrders()
    );
  }

  processOrders = action => () => {
    if ( action == 'reverse'
      && !window.confirm('Are you sure you want to delete and refund these orders? This action cannot be undone.')
    ) return false;

    const order_count = this.state.selection.length;
    const toast_id = createNotifcation( `${action}ing ${order_count} orders.` );

    this.props.processOrders( action, this.state.selection )
    .then( () => updateNotifcation( toast_id, `${order_count} orders updated!` ) )
    .then( this.loadOrders )
    .catch( e => updateNotifcation( toast_id, '', e.message, false ) );
  }

  toCSV = () => {
    const headers = [
      'Date', 'First Name', 'Last Name', 'Serial Number', 'Prize',
      'Miles', 'Qty', 'Total', 'Platoon'
    ];
    const rows = this.props.orders.map( order => [
      order.modified, order.first, order.last, order.user_serial,
      order.prize_name, order.points, order.quantity, order.total * -1,
      order.platoon
    ]);
    arrayToCSV( headers, rows, 'store_orders' );
  }

  render() {
    const { modal, selection, redeemed } = this.state;
    const { orders, loading, login } = this.props;

    let columns = getColumns( isBC( login.code, true ) );

    return (
      <div id='OrdersPage'>
        <Callout title='Store Orders'>
          <p>Create and manage orders coming in from Teachers, Kiosks and Parent Accounts</p>
          <strong>This page loads open orders. </strong>
          To see old orders, please press the "Load Redeemed Orders" button.
        </Callout>

        <ButtonGroup>
          <Button className='btn btn-primary' onClick={ this.toggleModal }>
            <FontAwesome icon='plus' /> Create Order
          </Button>
          
          <Button color='primary' onClick={ this.loadOrders }>
            <InlineSync loading={ loading } />{ ' ' }
            { loading ? 'Loading...' : 'Refresh' }
          </Button>

          <Button color='primary' onClick={ this.toggleOrderStatus }>
            <FontAwesome icon={redeemed ? 'store' : 'archive'} />{' '}
            Load { redeemed ? 'Open' : 'Redeemed' } Orders
          </Button>

          { canDownload( orders ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Orders (CSV/Excel)
            </Button>
          }

          { redeemed && 
            <Button color='primary' 
                onClick={ this.processOrders( 'unredeem' ) } 
                disabled={ selection.length === 0 }>
              <FontAwesome icon='undo' /> Un-redeem
            </Button>
          }

          { !redeemed && 
            <Button color='primary' 
                onClick={ this.processOrders( 'redeem' ) } 
                disabled={ selection.length === 0 }>
              <FontAwesome icon='box' /> Redeem
            </Button>
          }

          <Button color='primary'
              onClick={ this.processOrders( 'reverse' ) }
              disabled={ selection.length === 0 }>
            <FontAwesome icon='trash'/> Delete
          </Button>
          
        </ButtonGroup>

        <SelectTable 
          data={ orders } 
          columns={ columns }
          pageId='OrdersPage' 
          
          loading={ loading }

          getId={ this.getId }
          selection={ selection }
          maxSelectionSize={ orders.length }
          toggleRow={ this.toggleRow }
          toggleAll={ this.toggleAll } />

        <OrderModal
          isOpen={ modal }
          toggle={ this.toggleModal } />

      </div>
    );
  }
}

const mapStateToProps = ({ rewards, login }) => {
  const { orders } = rewards;
  return {
    ...orders,
    login: login.current_login
  }
};

const mapDispatchToProps = { getOrders, processOrders };

export default connect( mapStateToProps, mapDispatchToProps )( OrdersPage );
