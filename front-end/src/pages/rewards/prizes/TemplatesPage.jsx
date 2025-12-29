import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Button } from 'reactstrap';
import PrizeModal from './PrizeModal';
import { Navigate } from 'react-router-dom';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { getColumns } from './include/columns';
import { isHQ } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import {
  getTemplates, createTemplate, updateTemplate, uploadImage
} from 'store/rewards/prizes/operations';
// styles
import './include/prizes.scss';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const TemplatesPage = () => {
  const dispatch = useDispatch();
  const { templates, loading, login } = useSelector(({ rewards, login }) => ({
    ...rewards.prizes,
    login: login.current_login
  }));

  const [cropperModal, setCropperModal] = useState({ show: false, id: false, src: false });
  const [prizeModal, setPrizeModal] = useState({ show: false, template: {} });

  const loadTemplates = () => {
    dispatch(getTemplates())
      .catch(e => toast.error(e.message));
  }

  useEffect(() => {
    setTitle('Store Prize Templates');
    loadTemplates();
  }, [dispatch]);

  // update prizes in a modal ( not much data )
  const toggleTemplate = () => setPrizeModal(prev => ({ ...prev, show: false }));

  const editTemplate = template => () => {
    setPrizeModal({ show: true, template });
  }

  const newTemplate = () => {
    let modal = { ...prizeModal, show: true }
    // clear it if we had an existing prize loaded up. Otherwise keep the latest edits
    if (prizeModal.template.prize_id)
      modal.template = {};

    // update the modal
    setPrizeModal(modal);
  }

  // Edit images
  const toggleCropper = () => setCropperModal(prev => ({ ...prev, show: false }));

  const editPicture = id => ({ target }) => setCropperModal({
    show: true, id, src: target.src
  });

  const updateModalPicture = ({ image, image_id }) => {
    setPrizeModal(prev => ({
      ...prev,
      template: { ...prev.template, image, image_id }
    }));
  }

  const upload = formData => {
    let promise;
    // if we are editing a template...
    if (cropperModal.id) {
      promise = dispatch(updateTemplate(cropperModal.id, formData));
    } else {
      promise = dispatch(uploadImage(formData));
    }
    // return the promise
    return promise
      .then(prize => updateModalPicture(prize)) // update the prize modal to the new image
      .then(() => toggleCropper()) // toggle the image editing modal
      .catch(e => toast.error(e.message)); // catch and show any errors
  };

  const toCSV = () => {
    const headers = [
      'Prize Name', 'Default Miles', 'Default Stock', 'Default Status',
      'One Per Soldier', 'Last Updated',
    ];
    const rows = templates.map(prize => [
      prize.prize_name, prize.points, prize.prize_count,
      prize.is_active ? 'Active' : 'Disabled', prize.one_per_user ? 'Yes' : 'No',
      prize.modified,
    ]);
    return dataToCSV(headers, rows);
  }

  // Actions wrappers
  const handleUpdateTemplate = (id, data) => dispatch(updateTemplate(id, data));
  const handleCreateTemplate = (data) => dispatch(createTemplate(data));


  // non-admins go to their prizes
  if (!isHQ(login.code))
    return <Navigate to='/rewards/prizes' replace />;

  let columns = getColumns({
    editPicture,
    editPrize: editTemplate,
    isTemplate: true
  });

  return (
    <div id='TemplatesPage'>
      <ButtonBar>
        <Button className='btn btn-primary' onClick={newTemplate}>
          <FontAwesome icon='plus' /> Create Template
        </Button>
        <Button color='primary' onClick={loadTemplates}>
          <InlineSync loading={loading.templates} /> Refresh
        </Button>
        {canDownload(templates) &&
          <CSVLink
            data={toCSV()}
            filename={"store_templates.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Templates (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={templates}
        columns={columns}
        pageId='TemplatesPage'
        loading={loading.templates && !templates.length} />

      <PrizeModal isTemplate
        login={login}
        prize={prizeModal.template}
        isOpen={prizeModal.show}
        editPicture={editPicture}
        toggle={toggleTemplate}
        updatePrize={handleUpdateTemplate}
        createPrize={handleCreateTemplate} />

      <CropperModal
        fileName='image'
        src={cropperModal.src}
        isOpen={cropperModal.show}
        toggle={toggleCropper}
        uploadImage={upload} />

    </div>
  );
}

export default TemplatesPage;
