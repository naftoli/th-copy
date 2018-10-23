import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import PrizeModal from './PrizeModal';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { getColumns } from './include/columns';
import { isAdmin, isBC } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { 
  getPrizes, updatePrize, uploadImage,
  getTemplates, createPrize, setStoreOpen
} from 'store/rewards/prizes/operations';
// styles
import './include/prizes.scss';


class PrizesPage extends Component {

  state = { 
    cropperModal: { show: false, id: false, src: false },
    prizeModal: { show: false, prize: {} }
  };

  componentDidMount() { 
    setTitle( 'Store Prizes' );
    this.loadPrizes();
  }
  // Network
  loadPrizes = () => {
    this.props.getPrizes()
    .catch( e => toast.error( e.message ) );
    // load all templates if we can create prizes
    if ( !isAdmin( this.props.login.code ) )
      this.props.getTemplates()
      .catch( e => toast.error( e.message ) );
  }

  // update prizes in a modal ( not much data )
  togglePrize = () => this.setState({
    prizeModal: { ...this.state.prizeModal, show: false }
  });
  editPrize = ( prize ) => () => {
    this.setState({
      prizeModal: { show: true, prize }
    })
  }
  newPrize = () => {
    let modal = {  ...this.state.prizeModal, show: true }
    // clear it if we had an existing prize loaded up
    if ( this.state.prizeModal.prize.prize_id ) {
      modal.prize = {};
    }
    // update the modal
    this.setState({ prizeModal: modal })
  }

  toggleCropper = () => this.setState({
    cropperModal: { ...this.state.cropperModal, show: false }
  });
  editPicture = id => ({ target }) => this.setState({ cropperModal: {
    show: true, id, src: target.src }
  });
  
  upload = formData => {
    let promise;
    // if we are editing a prize...
    if ( this.state.cropperModal.id ) {
      promise = this.props.updatePrize( this.state.cropperModal.id, formData )
    } else {
      promise = this.props.uploadImage( formData );
    }
    // return the promise
    return promise
      .then( prize => this.updatePrizePicture( prize ) ) // update the prize modal to the new image
      .then( () => this.toggleCropper() ) // toggle the image editing modal
      .catch( e => toast.error( e.message ) ); // catch and show any errors
  };
  // update the image in the modals nested structure
  updatePrizePicture = ({ image, image_id }) => {
    this.setState({
      prizeModal: {
        ...this.state.prizeModal,
        prize: { ...this.state.prizeModal.prize, image, image_id }
      }
    });
  }

  updateToggle = ( key, id ) => e => {
    return this.props.updatePrize( id, { [key]: e.target.checked ? 1 : 0 } )
    .catch( e => toast.error( e.message ) );
  }
  toggleStore = () => {
    const { school_store } = this.props;
    if ( window.confirm( `Are you sure you want to ${ school_store ? 'close' : 'open' } your base store?` ) ) {
      this.props.setStoreOpen( !school_store )
      .then( () => toast.info( `Store ${ school_store ? 'closed' : 'opened' }` ) )
      .catch( e => toast.error( e.message ) );
    }
  }

  toCSV = () => {
    const headers = [
      'Prize Name', 'Miles', 'In Stock', 'Active',
      'One Per Soldier', 'Last Updated', 'Base Number', 'Base'
    ];
    const rows = this.props.prizes.map( prize => [
      prize.prize_name, prize.points, prize.prize_count, 
      prize.is_active ? 'Yes' : 'No', prize.one_per_user ? 'Yes' : 'No', 
      prize.modified, prize.school.school_number, prize.school.school_name
    ]);
    arrayToCSV( headers, rows, 'store_prizes' );
  }

  render() {
    const { prizeModal, cropperModal } = this.state;
    const { editPrize, editPicture, updateToggle } = this;
    const { 
      prizes, loading, login, templates, 
      school_store, updatePrize, createPrize 
    } = this.props;

    let columns = getColumns({
      editPrize, editPicture, updateToggle,
      showPlatoons: true
    });

    if ( isAdmin( login.code ) )
      columns.push(
        { Header: 'Base', id: 'base', accessor: prize => prize.school.school_name,
          Cell: props => <Link to={`/bm/base/${props.original.school.school_id}`}>{props.value}</Link>,
        }
      );

    // open-close store
    let storeButton;
    if ( school_store ) {
      storeButton = (
        <Button color='primary' onClick={ this.toggleStore }>
          <FontAwesome icon='store' /> Close Store
        </Button>
      );
    } else {
      storeButton = (
        <Button color='danger' onClick={ this.toggleStore }>
          <FontAwesome icon='store' /> Open Store
        </Button>
      );
    }

    return (
      <div id='PrizesPage' className='full-height'>
        <ButtonBar>
          { !isAdmin( login.code ) &&
            <Button className='btn btn-primary' onClick={ this.newPrize }>
              <FontAwesome icon='plus' /> Create Prize
            </Button>
          }
          
          <Button color='primary' onClick={ this.loadPrizes }>
            <InlineSync loading={ loading.prizes } /> Refresh
          </Button>
          
          { isBC( login.code, true ) && storeButton }

          { canDownload( prizes ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Prizes (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Table 
          data={ prizes } 
          columns={ columns } 
          loading={ loading.prizes && !prizes.length } 
          pageId='PrizesPage' />

        <PrizeModal
          login={ login }
          prize={ prizeModal.prize }
          isOpen={ prizeModal.show }
          toggle={ this.togglePrize }
          templates={ templates }
          editPicture={ editPicture }
          updatePrize={ updatePrize }
          createPrize={ createPrize } />

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
  getPrizes, updatePrize, createPrize,
  getTemplates, uploadImage, setStoreOpen
};

export default connect( mapStateToProps, mapDispatchToProps )( PrizesPage );
