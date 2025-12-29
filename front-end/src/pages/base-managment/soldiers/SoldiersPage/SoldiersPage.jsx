import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, FontAwesome } from 'components/ui';
// local modals
import BulkUploadModal from './BulkUploadModal';
import NewSoldierModal from './NewSoldierModal';
// functions
import is from 'is_js';
import { setTitle, canDownload } from 'functions/utils';
import { showError } from 'functions/notifications';
// styles
import './SoldiersPage.scss';
// state
import {
  uploadProfile, getSoldiers, createSoldier,
  updateSoldier, uploadSpreadsheet
} from 'store/base/soldiers/operations';
// data
import getColumns from './columns';
import { isBC } from 'functions/login';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

const defaultCropperModalSettings = {
  show: false, src: false, id: false
}

export const SoldiersPage = ({
  login, soldiers, loading, getSoldiers, uploadProfile, updateSoldier, createSoldier, uploadSpreadsheet
}) => {

  const [cropper, setCropper] = useState({ ...defaultCropperModalSettings });
  const [upload, setUpload] = useState({ show: false });
  const [create, setCreate] = useState({ show: false, image: {} });

  useEffect(() => {
    setTitle('View/Edit Soldiers');
    handleGetSoldiers();
  }, []);

  const handleGetSoldiers = () => {
    showError(getSoldiers());
  }

  // close the cropper modal
  const closeCropperModal = () => setCropper({
    ...defaultCropperModalSettings
  });

  // toggle the modals
  const toggleModal = key => () => {
    if (key === 'create') setCreate(prev => ({ ...prev, show: !prev.show }));
    if (key === 'upload') setUpload(prev => ({ ...prev, show: !prev.show }));
  };

  // handler for pressing the picture
  const editPicture = id => ({ target }) => setCropper({
    show: true, id, src: target.src
  });

  // handler for when images are updated
  const updatePicture = formData => {
    if (cropper.id)
      updateSoldier(cropper.id, formData);
    else
      uploadProfile(formData)
        .then(res => setCreate(prev => ({ ...prev, image: res })));
    // close the cropper modal
    closeCropperModal();
  }

  // update soldiers when the toggle is changed
  const updateToggle = (key, id) => e => showError(
    updateSoldier(
      id, { [key]: e.target.checked ? 1 : 0 }
    ));

  // handler for uploding the excel file
  const handleUploadSpreadsheet = (formData) => showError(
    uploadSpreadsheet(formData)
      .then(() => {
        setUpload({ show: false });
        return handleGetSoldiers();
      })
  );

  // create a new soldier
  const handleCreateSoldier = soldier => {
    return createSoldier(soldier)
      .then(() => {
        setCreate({ show: false, image: {} })
      })
  }

  // download the content as a CSV
  const toCSV = () => {
    const headers = [
      'Serial Number', 'First Name', 'Last Name', 'שם פרטי', 'שם משפחה', 'DOB', 'Gender', 'Registered',
      'Chayolei', 'Tehillim', 'Chidon', 'Platoon', 'Base', 'Rank', 'Family ID'
    ];
    // get the data
    const rows = soldiers.map(soldier => [
      soldier.user_serial, soldier.first, soldier.last, soldier.first_he, soldier.last_he, soldier.dob, soldier.gender,
      soldier.user_registered, soldier.chayolei, soldier.yan, soldier.chidon,
      soldier.platoon ? `="${soldier.platoon.name}"` : '-', soldier.school.school_name, soldier.rank_name, soldier.admin_id
    ]);
    return dataToCSV(headers, rows);
  }

  const columns = getColumns(login, editPicture, updateToggle);

  return (
    <div id='SoldiersPage' className='full-height'>

      <ButtonBar>
        <Button color='primary' onClick={toggleModal('create')}>
          <FontAwesome icon='plus' /> Add Soldier
        </Button>
        <Button color='primary' onClick={handleGetSoldiers}>
          <InlineSync loading={loading} /> Refresh
        </Button>
        {isBC(login.code, true) && is.not.mobile() && is.not.ios() && // only Base Commanders on desktops/tablets can upload
          <Button color='primary' onClick={toggleModal('upload')}>
            <FontAwesome icon='file-upload' /> Upload Soldier List
          </Button>
        }
        {canDownload(soldiers) &&
          <CSVLink
            data={toCSV()}
            filename={"soldiers.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Soldiers (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        columns={columns}
        data={soldiers}
        loading={loading && !soldiers.length}
        pageId='SoldiersPage' />

      <CropperModal
        src={cropper.src}
        isOpen={cropper.show}
        toggle={closeCropperModal}
        uploadImage={updatePicture} />

      <NewSoldierModal
        image={create.image}
        isOpen={create.show}
        onSubmit={handleCreateSoldier}
        toggle={toggleModal('create')}
        editPicture={editPicture(false)} />

      {/* Only show the uploader for base commanders */
        login.code === 'BC' &&
        <BulkUploadModal
          inst={login.inst_id}
          isOpen={upload.show}
          upload={handleUploadSpreadsheet}
          toggle={toggleModal('upload')} />
      }
    </div>
  );

}

export default connect(({ base, login }) => ({
  ...base.soldiers,
  login: login.current_login
}), {
  uploadProfile, getSoldiers, createSoldier,
  updateSoldier, uploadSpreadsheet
})(SoldiersPage);
