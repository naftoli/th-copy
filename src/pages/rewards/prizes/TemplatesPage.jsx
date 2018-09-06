import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Redirect } from 'react-router-dom';
import { Button, ButtonGroup } from 'reactstrap';
import PrizeModal from './PrizeModal';
import CropperModal from 'components/modals/CropperModal';
import { Table, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { getColumns } from './include/columns';
import { isHQ } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { 
  getTemplates, createTemplate, updateTemplate, uploadImage
} from 'store/rewards/prizes/operations';
// styles
import './include/prizes.scss';


class TemplatesPage extends Component {

  state = { 
    cropperModal: { show: false, id: false, src: false },
    prizeModal: { show: false, template: {} }
  };

  componentDidMount() {
    setTitle( 'Store Prize Templates' );
    this.loadTemplates(); 
  }
  // Network
  loadTemplates = () => {
    this.props.getTemplates()
    .catch( e => toast.error( e.message ) );
  }

  // update prizes in a modal ( not much data )
  toggleTemplate = () => this.setState({
    prizeModal: { ...this.state.prizeModal, show: false }
  });
  editTemplate = template => () => {
    this.setState({ prizeModal: { show: true, template } });
  }
  newTemplate = () => {
    let modal = {  ...this.state.prizeModal, show: true }
    // clear it if we had an existing prize loaded up. Otherwise keep the latest edits
    if ( this.state.prizeModal.template.prize_id )
      modal.template = {};

    // update the modal
    this.setState({ prizeModal: modal })
  }

  // Edit images
  toggleCropper = () => this.setState({
    cropperModal: { ...this.state.cropperModal, show: false }
  });
  editPicture = id => ({ target }) => this.setState({
    cropperModal: { show: true, id, src: target.src }
  });
  upload = formData => {
    let promise;
    // if we are editing a template...
    if ( this.state.cropperModal.id ) {
      promise = this.props.updateTemplate( this.state.cropperModal.id, formData );
    } else {
      promise = this.props.uploadImage( formData );
    }
    // return the promise
    return promise
      .then( prize => this.updateModalPicture( prize ) ) // update the prize modal to the new image
      .then( () => this.toggleCropper() ) // toggle the image editing modal
      .catch( e => toast.error( e.message ) ); // catch and show any errors
  };
  // update the image in the modals nested structure
  updateModalPicture = ({ image, image_id }) => {
    this.setState({
      prizeModal: {
        ...this.state.prizeModal,
        template: { ...this.state.prizeModal.template, image, image_id }
      }
    });
  }

  toCSV = () => {
    const headers = [
      'Prize Name', 'Default Miles', 'Default Stock', 'Default Status',
      'One Per Soldier', 'Last Updated',
    ];
    const rows = this.props.prizes.map( prize => [
      prize.prize_name, prize.points, prize.prize_count, 
      prize.is_active ? 'Active' : 'Disabled', prize.one_per_user ? 'Yes' : 'No', 
      prize.modified,
    ]);
    arrayToCSV( headers, rows, 'store_prizes' );
  }

  render() {
    // non-admins go to their prizes
    if ( !isHQ( this.props.login.code ) )
      return <Redirect to='/rewards/prizes' />;

    const { prizeModal, cropperModal } = this.state;
    const { editTemplate, editPicture } = this;
    const { templates, loading, login, updateTemplate, createTemplate  } = this.props;

    let columns = getColumns({
      editPicture,
      editPrize: editTemplate,
      isTemplate: true
    });

    return (
      <div id='TemplatesPage'>
        <ButtonGroup>
          <Button className='btn btn-primary' onClick={ this.newTemplate }>
            <FontAwesome icon='plus' /> Create Template
          </Button>
          <Button color='primary' onClick={ this.loadTemplates }>
            <InlineSync loading={ loading.templates } /> Refresh
          </Button>
          { canDownload( templates ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Templates (CSV/Excel)
            </Button>
          }
        </ButtonGroup>

        <Table 
          data={ templates } 
          columns={ columns } 
          pageId='TemplatesPage'
          loading={ loading.templates && !templates.length } />

        <PrizeModal isTemplate
          login={ login }
          prize={ prizeModal.template }
          isOpen={ prizeModal.show }
          editPicture={ editPicture }
          toggle={ this.toggleTemplate }
          updatePrize={ updateTemplate }
          createPrize={ createTemplate } />

        <CropperModal 
          fileName='image'
          src={ cropperModal.src }
          isOpen={ cropperModal.show }
          toggle={ this.toggleCropper }
          uploadImage={ this.upload } />

      </div>
    );
  }
}

const mapStateToProps = ({ rewards, login }) => {
  const { prizes } = rewards;
  return {
    ...prizes,
    login: login.current_login
  }
};

const mapDispatchToProps = {
  getTemplates, createTemplate,
  uploadImage,  updateTemplate
};

export default connect( mapStateToProps, mapDispatchToProps )( TemplatesPage );
