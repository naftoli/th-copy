import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import RegistrationModal from './RegistrationModal';
import { Row, Col, Button } from 'reactstrap'; 
import { ButtonBar, SelectTable, Callout, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { getPaymentProfiles } from 'store/payments/operations';
import { getSoldiers, registerSoldiers } from 'store/base/soldiers/registration/operations';
// styles
import './RegistrationPage.scss';

export class RegistrationPage extends Component {

  state = {
    total: 0,
    selection: [],
    showModal: false
  }
  
  componentDidMount(){ 
    setTitle('Soldier Registration');
    // if the soldiers are empty then get the soldiers
    if ( this.props.soldiers.length === 0 )
      this.getSoldiers();
    // get the current payment profiles
    this.props.getPaymentProfiles();
  }

  toCSV = () => {
    const headers = [
      'First Name', 'Last Name', 'Serial Number', 'Registration Fee', 'Platoon'
    ];
    const rows = this.props.soldiers.map( soldier => [
      soldier.first, soldier.last, soldier.user_serial, soldier.fee,
      soldier.platoon ? `="${soldier.platoon}"` : ''
    ]);
    arrayToCSV( headers, rows, 'not_registered_soldiers' );
  }
  // close the modal
  closeModal = () => this.setState({ showModal: false });
  // open the modal or just register the soldiers
  payAndRegister = () => {
    if ( this.state.total > 0 ) {
      return this.setState({ showModal: true });
    } else {
      return this.registerSoldiers();
    }
  }
  // get the id
  getId = row => row.user_id;
  // toggle a row in the table
  toggleRow = ( selection, { fee } ) => {
    if ( this.state.selection.length > selection.length )
      fee = -Math.abs(fee); // update the fee to a negative number if we reduced the length of items
    // update the state
    const total = this.state.total + fee;
    this.setState({ selection, total });
  };
  // toggle all the rows in the table
  toggleAll = ( selection, currentRecords ) => {
    let total = 0;
    if ( currentRecords.length > 0 )
      currentRecords.forEach(item => { total += item._original.fee; } ); // calculate the fee
      
    this.setState({ selection, total });
  };
  // get the soldiers
  getSoldiers = () => {
    this.props.getSoldiers()
    .catch( e => toast.error( e.message ) );
  }
  // register the soldiers
  registerSoldiers = ( payment = false ) => {
    this.setState({ showModal: false });
    // make sure there is more then one soldier
    if ( this.state.selection.length === 0 ) {
      return toast.error('Cannot Register 0 Soldiers.');
    }
    // register the soldiers
    this.props.registerSoldiers( this.state.selection, payment, this.state.total )
      .then( this.props.getSoldiers )
      .catch( e => toast.error( e.message ) );
  }

  render() {
    const { total, selection, showModal } = this.state;
    const { login, loading, soldiers, payments } = this.props;

    console.log( payments );

    // define table columns
    let columns = [
      { Header: 'First Name', accessor: 'first',
        Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link> },
      { Header: 'Last Name', accessor: 'last',
        Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link> },
      { Header: 'Serial Number', accessor: 'user_serial',
        Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link> },
      { Header: 'Registration Fee', accessor: 'fee' },
      { Header: 'Platoon', accessor: 'platoon' },
    ];
    // add base column for HQ
    if ( isAdmin(login.code) ) {
      columns.push( { Header: 'Base', accessor: 'school_name' } );
    }

    // render the page
    return (
      <div id='RegistrationPage'>
        <Callout title='Soldier Registration'>
          All unregistered soldiers are displayed below, along with the cost to register.<br/>
          Please select the Soldiers you are registering.
        </Callout>
        
        <ButtonBar>
          <Button
              color='primary'
              onClick={ this.payAndRegister }
              disabled={ selection.length === 0 }>
            <FontAwesome icon='dollar-sign' /> Pay and Register
          </Button>

          <Button color='primary' onClick={ this.getSoldiers }>
            <InlineSync loading={ loading } /> Refresh
          </Button>

          { canDownload( soldiers ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Page (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Row>
          <Col xs='12' sm='6'>
            <h2 id='total'>
              Total: ${ total.toLocaleString( navigator.language ) }
            </h2>
          </Col>
          <Col xs='12' sm='6'>
            <h2 id='total'>
              Soldiers: { selection.length.toLocaleString( navigator.language ) }
            </h2>
          </Col>
        </Row>

        <SelectTable 
          pageId='RegistrationPage' 
          data={ soldiers } columns={ columns }
          loading={ loading && !soldiers.length }

          getId={ this.getId }
          selection={ selection }
          maxSelectionSize={ soldiers.length }
          toggleRow={ this.toggleRow }
          toggleAll={ this.toggleAll } />

        <RegistrationModal
          isOpen={ showModal }
          payments={ payments }
          toggle={ this.closeModal }
          onSubmit={ this.registerSoldiers } />
      </div>
    )
  }
}

const mapStateToProps = ( { login, base, payments } ) => ({
  login: login.current_login,
  soldiers: base.soldiers.registration_soldiers,
  loading: base.soldiers.loading,
  payments: payments.payments
});

const mapDispatchToProps = {
  getSoldiers, registerSoldiers, getPaymentProfiles
}

export default connect(
  mapStateToProps, mapDispatchToProps
)( RegistrationPage );
