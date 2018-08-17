import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Callout, InlineSync, FontAwesome } from 'components/ui';
import { Checkbox } from 'components/inputs';
import { Link } from 'react-router-dom';
import { Row, Col, Button, ButtonGroup } from 'reactstrap'; 
import ReactTable from "react-table";
import RegistrationModal from './RegistrationModal';
// functions
import { toast } from 'react-toastify';
import { loginStoreChanged } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
import { defaultTableProps } from 'functions/tables';
import { getSoldiers, registerSoldiers } from 'store/soldiers/registration/operations';
// styles
import './RegistrationPage.scss';

export class RegistrationPage extends Component {

  state = {
    selection: [],  selectAll: false,
    total: 0, showModal: false
  }
  // refs
  checkboxTable = React.createRef();
  checkAll = null; // uses callback refs. will point directly ( not using .current )
  
  componentDidMount(){ 
    setTitle('Soldier Registration');
    const { soldiers, getSoldiers } = this.props;
    if ( soldiers.length === 0 )
      getSoldiers().catch( e => toast.error( e.message ) );
  }

  componentDidUpdate( prevProps ) {
    if ( loginStoreChanged( prevProps.login ) ) {
      this.props.getSoldiers().catch( e => toast.error( e.message ) );
      this.setState({ selectAll: false, selection: [], total: 0 });
    }
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

  toggleModal = () => this.setState({ showModal: !this.state.showModal });

  isSelected = user_id => this.state.selection.includes( user_id );

  toggleRow = ( { user_id, fee } ) => {
    // start off with the existing state ( with a new array to avoid errors )
    let selection = [ ...this.state.selection ];
    const keyIndex = selection.indexOf( user_id );
    // check to see if the key exists
    if (keyIndex >= 0) { // it does exist so we will remove it using destructing
      selection = [ ...selection.slice(0, keyIndex), ...selection.slice(keyIndex + 1) ];
      fee = -Math.abs(fee); // update the fee to a negative number
    } else { // it does not exist so add it
      selection.push(user_id);
    }
    // UI update
    this.checkAll.indeterminate = this.props.soldiers.length > selection.length && selection.length > 0;
    // update the state
    const total = this.state.total + fee;
    const selectAll = this.props.soldiers.length === selection.length
    this.setState({ selection, total, selectAll });
  };

  toggleAll = () => {
    // uses HOC to select all the currently visiable users in all pages ( not the ones filtered out )
    const selectAll = this.state.selectAll ? false : true;
    const selection = [];
    let total = 0;
    if (selectAll) {
      // we need to get at the internals of ReactTable
      // the 'sortedData' property contains the currently accessible records based on the filter and sort
      const currentRecords = this.checkboxTable.current.getResolvedState().sortedData;
      // we just push all the IDs onto the selection array
      currentRecords.forEach(item => {
        selection.push(item._original.user_id);
        total += item._original.fee; // add the fee
      });
    }
    this.setState({ selectAll, selection, total });
  };

  registerUsers = ( payment ) => {
    this.setState({ showModal: false });
    if ( this.state.selection.length === 0 ) {
      return toast.error('Cannot Register 0 Soldiers.');
    }
    this.props.registerSoldiers( this.state.selection, payment, this.state.total )
    .then( this.props.getSoldiers );
  }

  render() {
    const { login, loading, soldiers, getSoldiers } = this.props;
    const { selectAll, total, selection, showModal } = this.state;
    const { toCSV, isSelected, toggleRow, toggleAll, toggleModal } = this;
    // define table columns
    let columns = [
      { id: 'checkbox', accessor: '', width: 38,
        filterable: false, sortable: false, resizable: false,
        Cell: props => <Checkbox checked={ isSelected(props.original.user_id) }
          onChange={ () => {/* handled on line 110 for whole row*/} }/>, 
        Header: props => <Checkbox onChange={ toggleAll } checked={ selectAll }
          setRef={ ref => { this.checkAll = ref } } /> },
      { Header: 'First Name', accessor: 'first',
        Cell: props => <Link to={`/bm/users/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link> },
      { Header: 'Last Name', accessor: 'last',
        Cell: props => <Link to={`/bm/users/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link> },
      { Header: 'Serial Number', accessor: 'user_serial',
        Cell: props => <Link to={`/bm/users/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link> },
      { Header: 'Registration Fee', accessor: 'fee' },
      { Header: 'Platoon', accessor: 'platoon' },
    ];
    // add base column for HQ
    if ( ['HQ', 'CKIDS-ADMIN'].includes(login.code) ) {
      columns.push( { Header: 'Base', accessor: 'school_name' } );
    }
    // set props for each row in the table
    const getTrProps = ( state, row ) => {
      const selected = row ? isSelected( row.original.user_id ) : false;
      return {
        onClick: e => { e.preventDefault(); toggleRow( row.original ); },
        className: selected ? "selected-row" : ""
      }
    }
    // set the props for the table
    let tableProps = defaultTableProps( 'RegistrationPage', loading )
    tableProps = { 
      ...tableProps, columns, getTrProps, 
      data: soldiers, minRows: 10, defaultPageSize: 100, 
    }
    // render the page
    return (
      <div id='RegistrationPage'>
        <Callout title='Soldier Registration'>
          All unregistered soldiers are displayed below, along with the cost to register.<br/>
          Please select the Soldiers you are registering.
        </Callout>
        
        <ButtonGroup style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Button color='primary' onClick={toggleModal}>
            <FontAwesome icon='dollar-sign' /> Pay and Register
          </Button>
          <Button color='primary' onClick={ getSoldiers }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( soldiers ) &&
            <Button color='primary' onClick={ toCSV }>
              <FontAwesome icon='file-download' /> Download Page (CSV/Excel)
            </Button>
          }
        </ButtonGroup>
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
        <ReactTable { ...tableProps } ref={this.checkboxTable}/>

        <RegistrationModal isOpen={showModal} toggle={toggleModal} onSubmit={ this.registerUsers }/>
      </div>
    )
  }
}

const mapStateToProps = ( { login, soldiers } ) => ({
  login: login.current_login,
  soldiers: soldiers.registration_soldiers,
  loading: soldiers.loading
});

export default connect( mapStateToProps, { getSoldiers, registerSoldiers } )( RegistrationPage );
