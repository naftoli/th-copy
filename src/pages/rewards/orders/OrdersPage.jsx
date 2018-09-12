import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button, ButtonGroup } from 'reactstrap';
// import PrizeModal from './PrizeModal';
import { Callout, SelectTable, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isBC } from 'functions/login';
import { getColumns } from './include/columns';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { getOrders } from 'store/rewards/orders/operations';
// styles
import './include/orders.scss';

class OrdersPage extends Component {

  state = { 
    modal: { show: false },
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
    )
  }

  cancelOrders = () => {
    console.log( this.state.selection );
  }

  unredeemOrders = () => {
    console.log( this.state.selection );
  }


  redeemOrders = () => {
    console.log( this.state.selection );
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
    const { selection, redeemed } = this.state;
    const { orders, loading, login } = this.props;

    let columns = getColumns( isBC( login.code, true ) );

    return (
      <div id='OrdersPage'>
        <Callout title='Store Orders'>
          <p>Create and manage orders coming in from Teachers, Kiosks and Parent Accounts</p>
          <p>
            <strong>By Default this page only loads current orders that need to be fulfiled. </strong>
            To see old orders which have already been redeemed, please press the "Load Redeemed Orders" button.
          </p>
        </Callout>

        <ButtonGroup>
          <Button className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Order
          </Button>
          
          <Button color='primary' onClick={ this.loadOrders }>
            <InlineSync loading={ loading } />{ ' ' }
            { loading ? 'Loading...' : 'Refresh' }
          </Button>

          <Button color='primary' onClick={ this.toggleOrderStatus }>
            <FontAwesome icon={redeemed ? 'store-alt' : 'archive'} />{' '}
            Load { redeemed ? 'Current' : 'Redeemed' } Orders
          </Button>

          { canDownload( orders ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Orders (CSV/Excel)
            </Button>
          }

          { redeemed && 
            <Button color='primary' 
                onClick={ this.unredeemOrders } 
                disabled={ selection.length === 0 }>
              <FontAwesome icon='undo' /> Un-redeem
            </Button>
          }

          { !redeemed && 
            <Button color='primary' 
                onClick={ this.redeemOrders } 
                disabled={ selection.length === 0 }>
              <FontAwesome icon='box' /> Redeem
            </Button>
          }

          <Button color='primary'
              onClick={ this.cancelOrders }
              disabled={ selection.length === 0 }>
            <FontAwesome icon='ban'/> Cancel
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

        {/* <pre>
          { JSON.stringify( this.state, null, 2 ) }
        </pre> */}

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

const mapDispatchToProps = { getOrders };

export default connect( mapStateToProps, mapDispatchToProps )( OrdersPage );
