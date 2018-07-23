import React, { Component } from 'react';
import { connect } from 'react-redux';
import { LEGACY_URL, DEFAULT_PROFILE } from 'components/constants';
// components
import ReactTable from "react-table";
import { Link } from 'react-router-dom';
import { Callout } from '@blueprintjs/core';
import { Button, ButtonGroup } from 'reactstrap';
import ProfilePicture from 'components/ui/ProfilePicture';
import CropperModal from 'components/modals/CropperModal';
import BulkUploadModal from './BulkUploadModal';
// functions
import { toast } from 'react-toastify';
import is from 'is_js';
import { arrayToCSV, setTitle } from 'functions/utils';
// styles
import 'react-table/react-table.css';
import './UsersPage.scss';
// state
import { getSoldiers, updateSoldier, uploadSpreadsheet } from 'store/soldiers/operations';

export class UsersPage extends Component {

  state = { 
    cropperModalShow: false, cropperModalSrc: false, 
    cropperModalId: false,  uploadModalShow: false
  }
  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Soldiers' );
    if ( this.props.soldiers.length < 2 ) {
      this.props.getSoldiers();
    }
  }

  // if the soldier list is emptied while on the page... then refresh it
  componentDidUpdate( prevProps ) {
    const { type, id } = this.props.current_login;
    const { type: prevType, id: prevId } = prevProps.current_login;
    if ( type !== prevType || prevId !== id ) {
      this.props.getSoldiers();
    }
  }

  // handler for the modals
  closeCropperModal = () => { this.setState({ cropperModalShow: false }) }
  toggleUploadModal = () => { this.setState({ uploadModalShow: !this.state.uploadModalShow }) }

  // handler for pressing the picture
  editPicture = ( id ) => ( event ) => {
    this.setState({
      cropperModalShow: true, cropperModalId: id,
      cropperModalSrc: event.target.src.indexOf( DEFAULT_PROFILE ) >= 0 ? false : event.target.src      
    });
  }

  // handler for when images are updated
  updatePicture = ( formData ) => {
    this.setState({ cropperModalShow: false });
    this.props.updateSoldier( this.state.cropperModalId, formData )
  }

  // handler for uploding the excel file
  uploadSpreadsheet = ( formData ) => {
    this.setState({ uploadModalShow: false });
    this.props.uploadSpreadsheet( formData )
      .then( this.props.getSoldiers ); // refresh the users;
  }

  // scroll to the top of the table
  scrollToTop = () => { document.querySelector('#all-users .rt-tbody').scrollTop = 0; }

  // download the content as a CSV
  toCSV = () => {
    const toast_id = toast.info("Generating File...");
    const headers = [
      'Serial Number', 'First Name', 'Last Name', 'DOB', 'Gender', 'Registered', 
      'Chayolei', 'Tehillim', 'Chidon', 'Platoon', 'Base'
    ];
    // get the data
    const rows = this.props.soldiers.map( soldier => [
      soldier.user_serial, soldier.first, soldier.last, soldier.dob, soldier.gender, 
      soldier.user_registered, soldier.chayolei, soldier.yan, soldier.chidon,
      soldier.platoon.name, soldier.school.school_name
    ]);
    arrayToCSV( headers, rows, 'users' );
    toast.update( toast_id, {render: 'File Generated.'} );
  }
  
  // filter for case insensitivity and for any location in the string
  filter = ( filter, row ) => {
    const id = filter.pivotId || filter.id;
    return row[id] !== undefined ? String(row[id]).toLowerCase().includes(filter.value.toLowerCase()) : true
  }

  // render the page
  render() {
    const { current_login, soldiers, loading } = this.props;
    const { cropperModalShow, cropperModalSrc, uploadModalShow } = this.state;
    // define the table for the page
    const columns = [{
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={`${LEGACY_URL}${props.value}`} className='inline-profile' 
                        onClick={ this.editPicture( props.original.user_id ) }/>,
      className: 'profile-picture', width: 85, sortable: false,
      Filter: ({ filter, onChange }) =>
        <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
          onChange={event => onChange(event.target.value)}>
          <option value="all">Show All</option>
          <option value="yes">Has Profile</option>
          <option value="no">No Profile</option>
        </select>,
      filterMethod: ( filter, row ) => {
        if ( filter.value === 'all' ) return true;
        if ( filter.value === 'yes') return row[filter.id] !== DEFAULT_PROFILE;
        if ( filter.value === 'no') return row[filter.id] === DEFAULT_PROFILE;
      },
    },{
      Header: "First Name", accessor: 'first',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Last Name", accessor: 'last',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Serial Number", 
      accessor: 'user_serial',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      id: 'dob',  Header: 'Date Of Birth',
      accessor: user => user.dob ? new Date( user.dob ).toLocaleDateString() : '-',
    },{
      id: 'registered',  Header: 'Registered',
      accessor: user => user.user_registered ? new Date( user.user_registered ).toLocaleString() : null,
      filterMethod: ( filter, row ) => {
        if ( filter.value === 'all' ) return true;
        if ( filter.value === 'yes') return !!row[filter.id];
        if ( filter.value === 'no') return !row[filter.id];
      },
      Filter: ({ filter, onChange }) =>
        <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
          onChange={event => onChange(event.target.value)}>
          <option value="all">Show All</option>
          <option value="yes">Registered</option>
          <option value="no">Not Registered</option>
        </select>,
      sortMethod: ( a, b ) => {
        a = a === null || a === undefined ? '' : a;
        b = b === null || b === undefined ? '' : b;
        a = !a || new Date( a ); b =  !b || new Date( b );
        if (a > b) return 1;
        if (a < b) return -1;
        return 0;
      }
    },{
      id: 'platoon', Header: 'Platoon',
      accessor: user => user.platoon ? user.platoon.name : '-'
    }];
    // add a collumn for HQ ( and Networks )
    if ( current_login.code === 'HQ' ) {
      columns.push( { id: 'base', Header: 'Base', accessor: user => user.school.school_name } );
    }
    // page definition
    return (
      <div id="UsersPage">
        {/* User Guide */}
        <Callout intent="primary" title="View Soldiers">
          Click a Soldier's name or serial number to view and edit their account.<br/>
          Click on a Soldier's profile picture to edit or replace it.
        </Callout>
        {/* Action buttons */}
        <ButtonGroup style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Link to={`/users/new`} className="btn btn-primary" role="button">
           <i className="fas fa-plus" /> Add Soldier
          </Link>
          <Button color="primary" onClick={ this.props.getSoldiers }>
            <i className={`fas fa-redo-alt ${ !loading || 'fa-spin' }`}></i> Refresh
          </Button>
          { current_login.code === 'BC' && // only Base Commanders can upload
            <Button color="primary" onClick={ this.toggleUploadModal }>
              <i className="fas fa-file-upload" /> Upload Soldier List
            </Button>
          } { is.not.edge() && is.not.ie() && is.not.ios() &&
            <Button color="primary" onClick={ this.toCSV }>
              <i className="fas fa-file-download" /> Save Soldier List
            </Button>
          }
        </ButtonGroup>
        {/* Table with data */}
        <ReactTable data={ soldiers } columns={columns} filterable={true} className="-striped -highlight" 
          noDataText={ loading ? 'Loading...' : 'No Data' } defaultFilterMethod={ this.filter }
          onPageChange={ this.scrollToTop } onFilteredChange={ this.scrollToTop }/>
        {/* Modal to edit images */}
        <CropperModal isOpen={ cropperModalShow } src={ cropperModalSrc } 
          toggle={ this.closeCropperModal } uploadImage={ this.updatePicture }/>
        {/* Modal to upload soldiers */ 
          current_login.code === 'BC' &&
          <BulkUploadModal isOpen={ uploadModalShow } toggle={ this.toggleUploadModal }
            upload={ this.uploadSpreadsheet }/>
        }
      </div>
    );
  }

}

const mapStateToProps = ( state ) => {
  return {
    ...state.soldiers,
    current_login: state.login.current_login
  };
}

export default connect( 
  mapStateToProps, { getSoldiers, updateSoldier, uploadSpreadsheet } 
)( UsersPage );