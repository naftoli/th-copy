import React, { Component } from 'react';
import { connect } from 'react-redux';
import { LEGACY_URL } from 'components/constants';
// components
import ReactTable from "react-table";
import { Link } from 'react-router-dom';
import { Callout } from '@blueprintjs/core';
import { Button, ButtonGroup } from 'reactstrap';
import ProfilePicture from 'components/ui/ProfilePicture';
import CropperModal from 'components/modals/CropperModal';
// functions
import { toast } from 'react-toastify';
import arrayToCSV from 'functions/arrayToCSV';
// styles
import 'react-table/react-table.css';
import './AllUsers.scss';
// state
import { getSoldiers, updateSoldier } from 'store/soldiers/operations';

export class AllUsers extends Component {

  state = { showModal: false, modalSrc: false, modalId: false }

  // update the soldiers when the page loads
  componentDidMount(){
    this.props.getSoldiers();
  }

  // if the soldier list is emptied while on the page... then refresh it
  componentDidUpdate( prevProps ) {
    if ( prevProps.soldiers.length > 0 && this.props.soldiers.length === 0 ) {
      this.props.getSoldiers();
    }
  }

  // handler for the modal
  closeModal = () => { this.setState({ showModal: false }) }

  // handler for pressing the picture
  editPicture = ( id ) => ( event ) => {
    const default_picture = '/mobile/reg/images/profile-photo-default.jpg';
    this.setState({
      showModal: true,
      modalSrc: event.target.src.indexOf( default_picture ) >= 0 ? false : event.target.src,
      modalId: id
    });
  }

  // handler for when images are updated
  updatePicture = ( formData ) => {
    this.setState({ showModal: false });
    this.props.updateSoldier( this.state.modalId, formData );
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

  // render the page
  render() {
    const { current_login, soldiers, loading } = this.props;
    const { showModal, modalSrc } = this.state;
    // define the table for the page
    const columns = [{
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={`${LEGACY_URL}${props.value}`} className='inline-profile' 
                        onClick={ this.editPicture( props.original.user_id ) }/>,
      className: 'profile-picture', width: 85, filterable: false,
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
      accessor: user => user.user_registered ? "Yes" : 'No',
    },{
      id: 'platoon',
      Header: 'Platoon',
      accessor: user => user.platoon.name
    }];
    // add a collumn for HQ ( and Networks )
    if ( current_login.code === 'HQ' ) {
      columns.push( { id: 'base', Header: 'Base', accessor: user => user.school.school_name } );
    }
    // page definition
    return (
      <div id="all-users">
        <Callout intent="primary" title="View Soldiers">
          Click a Soldier's name or serial number to view and edit their account.<br/>
          Click on a Soldier's profile picture to edit or replace it.
        </Callout>
        <ButtonGroup style={{ margin: '10px', width: 'calc(100% - 20px', justifyContent: 'flex-end' }}>
          <Link to={`/users/new`} className="btn btn-primary" role="button">
           <i className="fas fa-plus" /> Add Soldier
          </Link>
          <Button color="primary" onClick={ this.toCSV }>
            <i className="fas fa-file-download" /> Save Soldier List
          </Button>
        </ButtonGroup>
        <ReactTable data={ soldiers } columns={columns} filterable={true} className="-striped -highlight"
          style={{ maxHeight: "85vh" }} noDataText={ loading ? 'Loading...' : 'No Data' } 
          onPageChange={ this.scrollToTop } onFilteredChange={ this.scrollToTop } />,
        <CropperModal isOpen={ showModal } src={ modalSrc } toggle={ this.closeModal } uploadImage={ this.updatePicture }/>
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
  mapStateToProps, { getSoldiers, updateSoldier } 
)( AllUsers );