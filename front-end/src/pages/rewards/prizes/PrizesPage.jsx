import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
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

export const PrizesPage = () => {
  const dispatch = useDispatch();
  const { prizes, loading, login, templates, school_store } = useSelector(({ rewards, login }) => ({
    ...rewards.prizes,
    login: login.current_login
  }));

  const [cropperModal, setCropperModal] = useState({ show: false, id: false, src: false });
  const [prizeModal, setPrizeModal] = useState({ show: false, prize: {} });
  const [selection, setSelection] = useState([]);
  const [discountModal, setDiscountModal] = useState({ show: false, discountType: 'points', discountAmount: '' });

  const loadPrizes = () => {
    dispatch(getPrizes())
      .then(() => setSelection([]))
      .catch(e => toast.error(e.message));
    // load all templates if we can create prizes
    if (!isAdmin(login.code))
      dispatch(getTemplates())
        .catch(e => toast.error(e.message));
  }

  useEffect(() => {
    setTitle('Store Prizes');
    loadPrizes();
  }, [dispatch]);

  // table
  const getId = row => row.prize_id;
  const toggleRow = (newSelection, row) => setSelection(newSelection);
  const toggleAll = (newSelection) => setSelection(newSelection);

  // toggle selection from the Discount cell only
  const toggleRowFromCell = (row) => {
    let newSelection = [...selection];
    const id = getId(row);
    const keyIndex = newSelection.indexOf(id);

    if (keyIndex >= 0)
      newSelection = [...newSelection.slice(0, keyIndex), ...newSelection.slice(keyIndex + 1)];
    else
      newSelection.push(id);

    setSelection(newSelection);
  }

  // update prizes in a modal ( not much data )
  const togglePrize = () => setPrizeModal(prev => ({ ...prev, show: false }));

  const editPrize = (prize) => () => {
    setPrizeModal({ show: true, prize });
  }

  const newPrize = () => {
    let modal = { ...prizeModal, show: true }
    // clear it if we had an existing prize loaded up
    if (prizeModal.prize.prize_id) {
      modal.prize = {};
    }
    // update the modal
    setPrizeModal(modal);
  }

  const toggleCropper = () => setCropperModal(prev => ({ ...prev, show: false }));

  const editPicture = id => ({ target }) => setCropperModal({
    show: true, id, src: target.src
  });

  const updatePrizePicture = ({ image, image_id }) => {
    setPrizeModal(prev => ({
      ...prev,
      prize: { ...prev.prize, image, image_id }
    }));
  }

  const upload = formData => {
    let promise;
    // if we are editing a prize...
    if (cropperModal.id) {
      promise = dispatch(updatePrize(cropperModal.id, formData));
    } else {
      promise = dispatch(uploadImage(formData));
    }
    // return the promise
    return promise
      .then(prize => updatePrizePicture(prize)) // update the prize modal to the new image
      .then(() => toggleCropper()) // toggle the image editing modal
      .catch(e => toast.error(e.message)); // catch and show any errors
  };

  const updateToggle = (key, id) => e => {
    return dispatch(updatePrize(id, { [key]: e.target.checked ? 1 : 0 }))
      .catch(e => toast.error(e.message));
  }

  const toggleStore = () => {
    if (window.confirm(`Are you sure you want to ${school_store ? 'close' : 'open'} your base store?`)) {
      dispatch(setStoreOpen(!school_store))
        .then(() => toast.info(`Store ${school_store ? 'closed' : 'opened'}`))
        .catch(e => toast.error(e.message));
    }
  }

  const toCSV = () => {
    const headers = [
      'Prize ID', 'Prize Name', 'Miles', 'In Stock', 'Active',
      'Max Per Soldier', 'Last Updated', 'Base Number', 'Base'
    ];
    const rows = prizes.map(prize => [
      prize.prize_id, prize.prize_name, prize.points, prize.prize_count,
      prize.is_active ? 'Yes' : 'No',
      prize.num_per_user || '',
      prize.modified, prize.school.school_number, prize.school.school_name
    ]);
    return dataToCSV(headers, rows);
  }

  const handleUpdateNumPerUser = (id, value) => {
    const numValue = value === '' ? 0 : (parseInt(value, 10) || 0);
    // Optimistically update the Redux state
    dispatch(updatePrizeLocally(id, { num_per_user: numValue }));
    dispatch(updatePrize(id, { num_per_user: numValue }))
      .catch(e => {
        toast.error(e.message);
        // Optionally revert the change if backend fails
        dispatch(getPrizes());
      });
  }

  // Discount functionality
  const toggleDiscountModal = () => setDiscountModal(prev => ({ ...prev, show: !prev.show }));

  const handleDiscountTypeChange = (e) => {
    setDiscountModal(prev => ({ ...prev, discountType: e.target.value }));
  };

  const handleDiscountAmountChange = (e) => {
    setDiscountModal(prev => ({ ...prev, discountAmount: e.target.value }));
  };

  const applyDiscount = async () => {
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
        return dispatch(updatePrize(prizeId, updateData));
      });

      const results = await Promise.all(promises);
      console.log('Update results:', results);

      toast.success(`Discount applied to ${selection.length} prize(s)`);
      setSelection([]);
      setDiscountModal({ show: false, discountType: 'points', discountAmount: '' });
    } catch (error) {
      console.error('Error applying discount:', error);
      toast.error('Failed to apply discount: ' + error.message);
    }
  };

  const clearDiscount = async () => {
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
        return dispatch(updatePrize(prizeId, updateData));
      });

      const results = await Promise.all(promises);
      console.log('Clear results:', results);

      toast.success(`Discount cleared from ${selection.length} prize(s)`);
      setSelection([]);
    } catch (error) {
      console.error('Error clearing discount:', error);
      toast.error('Failed to clear discount: ' + error.message);
    }
  };

  // Actions wrappers for modal
  const handleUpdatePrize = (id, data) => dispatch(updatePrize(id, data));
  const handleCreatePrize = (data) => dispatch(createPrize(data));
  const handleDeletePrize = (id) => dispatch(deletePrize(id));

  let columns = getColumns({
    editPrize, editPicture, updateToggle,
    showPlatoons: true,
    updateNumPerUser: handleUpdateNumPerUser,
    toggleRowFromCell: toggleRowFromCell
  });

  if (isAdmin(login.code))
    columns.push(
      {
        Header: 'Base', id: 'base', accessor: prize => prize.school.school_name,
        Cell: props => <Link to={`/bm/base/${props.original.school.school_id}`}>{props.value}</Link>,
      }
    );

  // open-close store
  let storeButton;
  if (school_store) {
    storeButton = (
      <Button color='primary' onClick={toggleStore}>
        <FontAwesome icon='store' /> Close Store
      </Button>
    );
  } else {
    storeButton = (
      <Button color='danger' onClick={toggleStore}>
        <FontAwesome icon='store' /> Open Store
      </Button>
    );
  }

  return (
    <div id='PrizesPage' className='full-height'>
      <ButtonBar>
        {!isAdmin(login.code) &&
          <Button className='btn btn-primary' onClick={newPrize}>
            <FontAwesome icon='plus' /> Create Prize
          </Button>
        }

        <Button color='primary' onClick={loadPrizes}>
          <InlineSync loading={loading.prizes} /> Refresh
        </Button>

        {selection.length > 0 && (
          <React.Fragment>
            <Button color='success' onClick={toggleDiscountModal}>
              <FontAwesome icon='percent' /> Apply Discount ({selection.length})
            </Button>
            <Button color='warning' onClick={clearDiscount}>
              <FontAwesome icon='times' /> Clear Discount
            </Button>
          </React.Fragment>
        )}

        {isBC(login.code, true) && storeButton}

        {canDownload(prizes) &&
          <CSVLink
            data={toCSV()}
            filename={"store_prizes.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Prizes (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <SelectTable
        data={prizes}
        getId={getId}
        pageId='PrizesPage'
        columns={columns}
        loading={loading.prizes && !prizes.length}
        selection={selection}
        toggleRow={toggleRow}
        toggleAll={toggleAll}
        getTrProps={(state, row) => {
          const selected = row ? selection.includes(getId(row.original)) : false;
          return { className: selected ? 'selectable selected-row' : 'selectable' };
        }}
        checkboxTogglesRow
        maxSelectionSize={prizes.length} />

      <PrizeModal
        login={login}
        prize={prizeModal.prize}
        isOpen={prizeModal.show}
        toggle={togglePrize}
        templates={templates}
        editPicture={editPicture}
        updatePrize={handleUpdatePrize}
        deletePrize={handleDeletePrize}
        createPrize={handleCreatePrize} />

      <CropperModal
        fileName='image'
        src={cropperModal.src}
        isOpen={cropperModal.show}
        toggle={toggleCropper}
        uploadImage={upload} />

      {/* Discount Modal */}
      <Modal isOpen={discountModal.show} toggle={toggleDiscountModal}>
        <ModalHeader toggle={toggleDiscountModal}>
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
                    onChange={handleDiscountTypeChange}
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
                    onChange={handleDiscountAmountChange}
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
          <Button color="secondary" onClick={toggleDiscountModal}>
            Cancel
          </Button>
          <Button color="primary" onClick={applyDiscount}>
            Apply Discount
          </Button>
        </ModalFooter>
      </Modal>

    </div>
  );
}

export default PrizesPage;
