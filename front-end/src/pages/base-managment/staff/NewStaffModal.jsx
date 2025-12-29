import React, { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Modal, ModalHeader, ModalBody, ModalFooter, Alert, Collapse } from 'reactstrap';
// rows
import EditStaffRow from './rows/EditStaffRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { SaveButton } from 'components/buttons';
import { createStaff, getStaff } from 'store/base/staff/operations';

const initialState = {
  staff: {
    username: '', password: '', email: '',
    title: '', first: '', last: '', work: '', cell: '',
  },
  auth: {},
  error: false,
  saving: false
}

const NewStaffModal = ({ isOpen, toggle }) => {
  const dispatch = useDispatch();
  const login = useSelector(state => state.login.current_login);

  const [staff, setStaff] = useState({ ...initialState.staff });
  const [auth, setAuth] = useState({});
  const [error, setError] = useState(false);
  const [saving, setSaving] = useState(false);

  // update state.staff
  const updateStaff = ({ target }) => {
    setStaff(prev => ({ ...prev, [target.name]: target.value }));
  }

  // update state.auth
  const handleSetAuth = authData => setAuth(authData);

  // onSubmit
  const handleCreateStaff = (e) => {
    e.preventDefault();

    setError(false);
    setSaving(true);

    dispatch(createStaff({ ...staff, auth }))
      .then(() => {
        toggle();
        dispatch(getStaff());
        setStaff({ ...initialState.staff });
        setAuth({});
      })
      .catch(({ message }) => {
        setError(message);
        if (!isOpen) toast.error(message);
      })
      .then(() => setSaving(false));

  }

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='NewStaffModal'>

      <ModalHeader toggle={toggle}>
        Create Staff Account
      </ModalHeader>

      <form onSubmit={handleCreateStaff}>
        <ModalBody>
          <EditStaffRow
            required
            {...staff}
            onChange={updateStaff} />

          <CreatePositionRow
            login={login}
            onChange={handleSetAuth}
            isAdmin={isAdmin(login.code)} />

          <Collapse isOpen={!!error}>
            <div id='errors'>
              <Alert color='danger'>{error}</Alert>
            </div>
          </Collapse>
        </ModalBody>

        <ModalFooter>
          <SaveButton text='Create' saving={saving} />
        </ModalFooter>
      </form>
    </Modal>
  );
}

export default NewStaffModal;
