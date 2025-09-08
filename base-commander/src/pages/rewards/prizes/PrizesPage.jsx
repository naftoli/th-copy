import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button, Modal, ModalHeader, ModalBody, ModalFooter, Form, FormGroup, Label, Input, Row, Col } from 'reactstrap';
import { Link } from 'react-router-dom';
import PrizeModal from './PrizeModal';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, SelectTable, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { getColumns } from './include/columns';
import { isAdmin, isBC } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import { 
  getPrizes, updatePrize, uploadImage,
  getTemplates, createPrize, setStoreOpen, deletePrize,
  updatePrizeLocally
} from 'store/rewards/prizes/operations';
// styles
import './include/prizes.scss';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

class PrizesPage extends Component {

  state = { 
    cropperModal: { show: false, id: false, src: false },
    prizeModal: { show: false, prize: {} },
    selection: [],
    discountModal: { show: false, discountType: 'points', discountAmount: '' }
  };

  componentDidMount() { 
    setTitle( 'Store Prizes' );
    this.loadPrizes();
  }

  // refs
  checkboxTable = React.createRef();
  checkAll = null;

  // Network
  loadPrizes = () => {
    this.props.getPrizes()
    .then( this.setState({ selection: [] }))
    .catch( e => toast.error( e.message ) );
    // load all templates if we can create prizes
    if ( !isAdmin( this.props.login.code ) )
      this.props.getTemplates()
      .catch( e => toast.error( e.message ) );
  }

  // table
  getId = row => row.prize_id;
  toggleRow = ( selection, row ) => this.setState({ selection });
  toggleAll = ( selection ) => this.setState({ selection });

  // toggle selection from the Discount cell only
  toggleRowFromCell = ( row ) => {
    let selection = [ ...this.state.selection ];
    const id = this.getId( row );
    const keyIndex = selection.indexOf( id );

    if ( keyIndex >= 0 )
      selection = [ ...selection.slice( 0, keyIndex ), ...selection.slice( keyIndex + 1 ) ];
    else
      selection.push( id );

    this.setState({ selection });
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
      'Prize ID', 'Prize Name', 'Miles', 'In Stock', 'Active',
      'Max Per Soldier', 'Last Updated', 'Base Number', 'Base'
    ];
    const rows = this.props.prizes.map( prize => [
      prize.prize_id, prize.prize_name, prize.points, prize.prize_count,
      prize.is_active ? 'Yes' : 'No',
      prize.num_per_user || '',
      prize.modified, prize.school.school_number, prize.school.school_name
    ]);
    //arrayToCSV( headers, rows, 'store_prizes' );
    return dataToCSV( headers, rows );
  }

  updateNumPerUser = (id, value) => {
    const numValue = value === '' ? 0 : (parseInt(value, 10) || 0);
    // Optimistically update the Redux state
    this.props.updatePrizeLocally(id, { num_per_user: numValue });
    this.props.updatePrize(id, { num_per_user: numValue })
      .catch(e => {
        toast.error(e.message);
        // Optionally revert the change if backend fails
        this.props.getPrizes();
      });
  }

  // Discount functionality
  toggleDiscountModal = () => this.setState({
    discountModal: { ...this.state.discountModal, show: !this.state.discountModal.show }
  });

  handleDiscountTypeChange = (e) => {
    this.setState({
      discountModal: { ...this.state.discountModal, discountType: e.target.value }
    });
  };

  handleDiscountAmountChange = (e) => {
    this.setState({
      discountModal: { ...this.state.discountModal, discountAmount: e.target.value }
    });
  };

  applyDiscount = async () => {
    const { selection, discountModal } = this.state;
    const { discountType, discountAmount } = discountModal;
    
    if (selection.length === 0) {
      toast.error('Please select at least one prize');
      return;
    }
    
    if (!discountAmount || discountAmount <= 0) {
      toast.error('Please enter a valid discount amount');
      return;
    }

    console.log('Applying discount:', { selection, discountType, discountAmount });

    try {
      // Apply discount to all selected prizes
      const promises = selection.map(prizeId => {
        const updateData = {
          discount_amount: parseInt(discountAmount, 10),
          discount_type: discountType
        };
        console.log(`Updating prize ${prizeId} with:`, updateData);
        return this.props.updatePrize(prizeId, updateData);
      });
      
      const results = await Promise.all(promises);
      console.log('Update results:', results);
      
      toast.success(`Discount applied to ${selection.length} prize(s)`);
      this.setState({
        selection: [],
        discountModal: { show: false, discountType: 'points', discountAmount: '' }
      });
    } catch (error) {
      console.error('Error applying discount:', error);
      toast.error('Failed to apply discount: ' + error.message);
    }
  };

  clearDiscount = async () => {
    const { selection } = this.state;
    
    if (selection.length === 0) {
      toast.error('Please select at least one prize');
      return;
    }

    console.log('Clearing discount for prizes:', selection);

    try {
      // Clear discount for all selected prizes
      const promises = selection.map(prizeId => {
        const updateData = {
          discount_amount: 0,
          discount_type: null
        };
        console.log(`Clearing discount for prize ${prizeId} with:`, updateData);
        return this.props.updatePrize(prizeId, updateData);
      });
      
      const results = await Promise.all(promises);
      console.log('Clear results:', results);
      
      toast.success(`Discount cleared from ${selection.length} prize(s)`);
      this.setState({ selection: [] });
    } catch (error) {
      console.error('Error clearing discount:', error);
      toast.error('Failed to clear discount: ' + error.message);
    }
  };

  render() {
    const { prizeModal, cropperModal, selection, discountModal } = this.state;
    const { editPrize, editPicture, updateToggle } = this;
    const { 
      prizes, loading, login, templates, 
      school_store, updatePrize, createPrize, deletePrize
    } = this.props;

    let columns = getColumns({
      editPrize, editPicture, updateToggle,
      showPlatoons: true,
      updateNumPerUser: this.updateNumPerUser,
      toggleRowFromCell: this.toggleRowFromCell
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
          
          { selection.length > 0 && (
            <React.Fragment>
              <Button color='success' onClick={ this.toggleDiscountModal }>
                <FontAwesome icon='percent' /> Apply Discount ({selection.length})
              </Button>
              <Button color='warning' onClick={ this.clearDiscount }>
                <FontAwesome icon='times' /> Clear Discount
              </Button>
            </React.Fragment>
          )}
          
          { isBC( login.code, true ) && storeButton }

          { canDownload( prizes ) &&
            <CSVLink
              data = { this.toCSV() }
              filename = { "store_prizes.csv" }
              target = "_blank"
            >
              <Button color='primary'>
                <FontAwesome icon='file-download' /> Download Prizes (CSV/Excel)              
              </Button>
            </CSVLink>
          }
        </ButtonBar>

        <SelectTable 
          data={ prizes } 
          getId={ this.getId }
          pageId='PrizesPage'
          columns={ columns }
          loading={ loading.prizes && !prizes.length }
          selection={ selection }
          toggleRow={ this.toggleRow }
          toggleAll={ this.toggleAll }
          getTrProps={ (state, row) => {
            const selected = row ? this.state.selection.includes( this.getId( row.original ) ) : false;
            return { className: selected ? 'selectable selected-row' : 'selectable' };
          } }
          checkboxTogglesRow
          maxSelectionSize={ prizes.length } />

        <PrizeModal 
          login={ login }
          prize={ prizeModal.prize }
          isOpen={ prizeModal.show }
          toggle={ this.togglePrize }
          templates={ templates }
          editPicture={ editPicture }
          updatePrize={ updatePrize }
          deletePrize={ deletePrize }
          createPrize={ createPrize } />

        <CropperModal 
          fileName='image'
          src={ cropperModal.src }
          isOpen={ cropperModal.show }
          toggle={ this.toggleCropper }
          uploadImage={ this.upload } />

        {/* Discount Modal */}
        <Modal isOpen={discountModal.show} toggle={this.toggleDiscountModal}>
          <ModalHeader toggle={this.toggleDiscountModal}>
            Apply Discount to {selection.length} Prize(s)
          </ModalHeader>
          <ModalBody>
            <Form>
              <Row>
                <Col md={6}>
                  <FormGroup>
                    <Label for="discountType">Discount Type</Label>
                    <Input
                      type="select"
                      id="discountType"
                      value={discountModal.discountType}
                      onChange={this.handleDiscountTypeChange}
                    >
                      <option value="points">Miles Discount</option>
                      <option value="percent">Percentage Off</option>
                    </Input>
                  </FormGroup>
                </Col>
                <Col md={6}>
                  <FormGroup>
                    <Label for="discountAmount">
                      {discountModal.discountType === 'points' ? 'Miles Discount' : 'Percentage Off'}
                    </Label>
                    <Input
                      type="number"
                      id="discountAmount"
                      value={discountModal.discountAmount}
                      onChange={this.handleDiscountAmountChange}
                      min="0"
                      step={discountModal.discountType === 'percent' ? "0.1" : "1"}
                      placeholder={discountModal.discountType === 'points' ? "Enter miles" : "Enter percentage"}
                    />
                  </FormGroup>
                </Col>
              </Row>
            </Form>
          </ModalBody>
          <ModalFooter>
            <Button color="secondary" onClick={this.toggleDiscountModal}>
              Cancel
            </Button>
            <Button color="primary" onClick={this.applyDiscount}>
              Apply Discount
            </Button>
          </ModalFooter>
        </Modal>

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
  getPrizes, updatePrize, createPrize, deletePrize,
  getTemplates, uploadImage, setStoreOpen,
  updatePrizeLocally
};

export default connect( mapStateToProps, mapDispatchToProps )( PrizesPage );
