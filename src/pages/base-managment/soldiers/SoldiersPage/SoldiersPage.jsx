import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, FontAwesome } from 'components/ui';
// local modals
import BulkUploadModal from './BulkUploadModal';
import NewSoldierModal from './NewSoldierModal';
// functions
import is from 'is_js';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
import { showError } from 'functions/notifications';
// styles
import './SoldiersPage.scss';
// state
import { 
  getSoldiers, updateSoldier, uploadSpreadsheet 
} from 'store/base/soldiers/operations';
// data
import getColumns from './columns';

const defaultCropperModalSettings = {
  show: false,  src: false, id: false
}

export class SoldiersPage extends Component {
  // initial state
  state = {
    cropper: {
      ...defaultCropperModalSettings
    },
    upload: { show: false },
    create: { show: true },
  }
  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Soldiers' );
    this.getSoldiers();
  }

  getSoldiers = () => {
    showError( this.props.getSoldiers() );
  }

  // close the cropper modal
  closeCropperModal = () => this.setState({ cropper: {
    ...defaultCropperModalSettings
  } } );
  // toggle the modals
  toggleModal = key => () => this.setState({
    [ key ]: { show: !this.state[ key ].show }
  });

  // handler for pressing the picture
  editPicture = id => ({ target }) => this.setState({ 
    cropper: { show: true, id, src: target.src } 
  });

  // handler for when images are updated
  updatePicture = formData => {
    const { id } = this.state.cropper;
    if ( id )
      this.props.updateSoldier( id, formData );
    else {
      debugger;
    }
    this.closeCropperModal();
  }

  updateToggle = ( key, id ) => e => {
    return showError( this.props.updateSoldier(
      id, { [key]: e.target.checked ? 1 : 0 }
    ));
  }

  // handler for uploding the excel file
  uploadSpreadsheet = ( formData ) => showError( 
    this.props.uploadSpreadsheet( formData )
    .then( () => {
      this.setState({ upload: { show: false } }); 
      return this.getSoldiers();
    })
  );

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
    const { cropper, upload, create } = this.state;
    const columns = getColumns( current_login, this.editPicture, this.updateToggle );
    // page definition
    return (
      <div id='SoldiersPage' className='full-height'>

        <ButtonBar>
          <Button color='primary' onClick={ this.toggleModal('create') }>
           <FontAwesome icon='plus' /> Add Soldier
          </Button>
          <Button color='primary' onClick={ this.getSoldiers }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { current_login.code === 'BC' && is.not.mobile() && is.not.ios() && // only Base Commanders on desktops/tablets can upload
            <Button color='primary' onClick={ this.toggleModal('upload') }>
              <FontAwesome icon='file-upload' /> Upload Soldier List
            </Button>
          } { canDownload( soldiers ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Soldiers (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Table 
          columns={ columns } 
          data={ soldiers } 
          loading={ loading && !soldiers.length } 
          pageId='SoldiersPage' />

        <CropperModal
          src={ cropper.src } 
          isOpen={ cropper.show }
          toggle={ this.closeCropperModal }
          uploadImage={ this.updatePicture } />

        <NewSoldierModal
          isOpen={ create.show } 
          toggle={ this.toggleModal('create') } />

        {/* Only show the uploader for base commanders */ 
          current_login.code === 'BC' &&
          <BulkUploadModal
            isOpen={ upload.show }
            upload={ this.uploadSpreadsheet }
            toggle={ this.toggleModal('upload') } />
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
)( SoldiersPage );
