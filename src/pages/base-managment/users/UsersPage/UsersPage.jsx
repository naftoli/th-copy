import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import BulkUploadModal from './BulkUploadModal';
import { Button } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, Callout, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import is from 'is_js';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// styles
import './UsersPage.scss';
// state
import { 
  getSoldiers, updateSoldier, uploadSpreadsheet 
} from 'store/base/soldiers/operations';
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
      this.getSoldiers();
    }
  }

  getSoldiers = () => {
    this.props.getSoldiers()
    .catch( e => toast.error( e.message, { autoClose: 10000 } ) );
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

  updateToggle = ( key, id ) => e => {
    return this.props.updateSoldier( id, { [key]: e.target.checked ? 1 : 0 } )
    .catch( e => toast.error( e.message ) );
  }

  // handler for uploding the excel file
  uploadSpreadsheet = ( formData ) => {
    return this.props.uploadSpreadsheet( formData )
    .then( () => {
      this.setState({ uploadModalShow: false }); 
      return this.getSoldiers();
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
    const columns = getColumns( current_login, this.editPicture, this.updateToggle );
    // page definition
    return (
      <div id='UsersPage' className='full-height'>
        {/* User Guide */}
        <Callout title='View Soldiers'>
          Click a Soldier's name or serial number to view and edit their account.<br/>
          Click on a Soldier's profile picture to edit or replace it.
        </Callout>
        {/* Action buttons */}
        <ButtonBar>
          <Link to={`${match.path}/new`} className='btn btn-primary' role='button'>
           <FontAwesome icon='plus' /> Add Soldier
          </Link>
          <Button color='primary' onClick={ this.getSoldiers }>
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
        </ButtonBar>
        {/* Table with data */}
        <Table 
          columns={ columns } 
          data={ soldiers } 
          loading={ loading && !soldiers.length } 
          pageId='UsersPage' />
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

const mapStateToProps = ({ base, login }) => {
  return {
    ...base.soldiers,
    current_login: login.current_login
  };
}

export default connect( 
  mapStateToProps, { getSoldiers, updateSoldier, uploadSpreadsheet } 
)( UsersPage );
