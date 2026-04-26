import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
// components
import { Button } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { ButtonBar, Table, InlineSync, FontAwesome } from 'components/ui';
// functions
import getColumns from './includes/columns';
import { isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import { showError } from 'functions/notifications';
import { getBases, updateBase } from 'store/base/bases/operations';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const BasesPage = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { bases, loading } = useSelector(state => state.base.bases);
  const login = useSelector(state => state.login.current_login);
  const [modal, setModal] = useState({ show: false, id: false, src: false });

  const basePath = '/bm/base';

  // load the contents
  useEffect(() => {
    setTitle('View / Edit Bases');
    // if we are not an admin, just go straight to our base
    if (!isAdmin(login.code)) {
      navigate(`${basePath}/${login.id}`, { replace: true });
    } else {
      dispatch(getBases());
    }
  }, [dispatch, login, navigate]);

  const loadBases = () => {
    if (!isAdmin(login.code)) {
      navigate(`${basePath}/${login.id}`, { replace: true });
    }
    dispatch(getBases());
  }

  // update base logos from master page
  const toggle = () => setModal(prev => ({ ...prev, show: !prev.show }));

  // open the modal
  const editPicture = id => ({ target }) => {
    setModal({ show: true, id, src: target.src });
  }

  // upload the picture and update the base
  const upload = formData => showError(
    dispatch(updateBase(modal.id, formData))
  );

  // update the the base in the background
  const updateToggle = (key, id) => event => showError(
    dispatch(updateBase(id, { [key]: event.target.checked ? 1 : 0 }))
  );

  const toCSV = () => {
    const headers = [
      'Base Number', 'Base Name', 'Base City', 'Base State', 'Base Country', 'Soldiers',
    ];
    // get the data
    const rows = bases.map(base => [
      base.school_number, base.school_name, base.school_city, base.school_state, base.school_country, base.soldier_count
    ]);
    return dataToCSV(headers, rows);
  }

  let columns = getColumns(basePath, editPicture, updateToggle);

  return (
    <div id='BasesPage'>
      <ButtonBar>
        <Button color='primary' onClick={loadBases}>
          <InlineSync loading={loading} /> Refresh
        </Button>
        {canDownload(bases) &&
          <CSVLink
            data={toCSV()}
            filename={"bases.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Bases (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={bases}
        columns={columns}
        loading={loading && !bases.length}
        pageId='BasesPage' />

      <CropperModal
        fileName='logo' viewMode={0}
        isOpen={modal.show} src={modal.src}
        toggle={toggle} uploadImage={upload} />
    </div>
  );
}

export default BasesPage;
