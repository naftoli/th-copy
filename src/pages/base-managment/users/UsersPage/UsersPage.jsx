import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import ReactTable from 'react-table';
import { Link } from 'react-router-dom';
import { Callout, FontAwesome } from 'components/ui';
import BulkUploadModal from './BulkUploadModal';
import { Button, ButtonGroup } from 'reactstrap';
import { InlineSync } from 'components/ui/loading';
import CropperModal from 'components/modals/CropperModal';
// functions
// import { toast } from 'react-toastify';
import is from 'is_js';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
import { loginStoreChanged } from 'functions/login';
import { filter, scrollToTop } from 'functions/tables';
// styles
import './UsersPage.scss';
// state
import { getSoldiers, updateSoldier, uploadSpreadsheet } from 'store/soldiers/operations';
// data
import getColumns from './columns';

export class UsersPage extends Component {
  // initial state
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
    if ( loginStoreChanged( prevProps.current_login ) )
      this.props.getSoldiers();
  }

  // handler for the modals
  closeCropperModal = () => { this.setState({ cropperModalShow: false }) }
  toggleUploadModal = () => { this.setState({ uploadModalShow: !this.state.uploadModalShow }) }

  // handler for pressing the picture
  editPicture = ( id ) => ( event ) => {
    this.setState({
      cropperModalShow: true, cropperModalId: id,
      cropperModalSrc: event.target.src
    });
  }

  // handler for when images are updated
  updatePicture = ( formData ) => {
    this.setState({ cropperModalShow: false });
    this.props.updateSoldier( this.state.cropperModalId, formData )
  }

  // handler for uploding the excel file
  uploadSpreadsheet = ( formData ) => {
    return this.props.uploadSpreadsheet( formData )
    .then( () => {
      this.setState({ uploadModalShow: false }); 
      return this.props.getSoldiers();
    });
  }

  // download the content as a CSV
  toCSV = () => {
    const headers = [
      'Serial Number', 'First Name', 'Last Name', 'DOB', 'Gender', 'Registered', 
      'Chayolei', 'Tehillim', 'Chidon', 'Platoon', 'Base'
    ];
    // get the data
    const rows = this.props.soldiers.map( soldier => [
      soldier.user_serial, soldier.first, soldier.last, soldier.dob, soldier.gender, 
      soldier.user_registered, soldier.chayolei, soldier.yan, soldier.chidon,
      soldier.platoon ? `="${soldier.platoon.name}"` : '-', soldier.school.school_name
    ]);
    arrayToCSV( headers, rows, 'soldiers' );
  }

  // render the page
  render() {
    const { current_login, soldiers, loading, match } = this.props;
    const { cropperModalShow, cropperModalSrc, uploadModalShow } = this.state;
    const columns = getColumns( current_login.code, this.editPicture );
    const onChange = scrollToTop('UsersPage');
    const tableProps = {
      data: soldiers, columns, className: "-striped -highlight", 
      noDataText: loading ? 'Loading...' : 'No Data', 
      filterable: true, defaultFilterMethod: filter,
      minRows: soldiers.length ? 5 : 15, defaultPageSize: is.mobile() || is.tablet() ? 20 : 50,
      onPageChange: onChange, onFilteredChange: onChange
    }
    // page definition
    return (
      <div id='UsersPage'>
        {/* User Guide */}
        <Callout title='View Soldiers'>
          Click a Soldier's name or serial number to view and edit their account.<br/>
          Click on a Soldier's profile picture to edit or replace it.
        </Callout>
        {/* Action buttons */}
        <ButtonGroup>
          <Link to={`${match.path}/new`} className='btn btn-primary' role='button'>
           <FontAwesome icon='plus' /> Add Soldier
          </Link>
          <Button color='primary' onClick={ this.props.getSoldiers }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { current_login.code === 'BC' && is.not.mobile() && is.not.ios() && // only Base Commanders on desktops/tablets can upload
            <Button color='primary' onClick={ this.toggleUploadModal }>
              <FontAwesome icon='file-upload' /> Upload Soldier List
            </Button>
          } { canDownload( soldiers ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Soldiers (CSV/Excel)
            </Button>
          }
        </ButtonGroup>
        {/* Table with data */}
        <ReactTable { ...tableProps } />
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
