import React, { useState, useEffect, useMemo } from 'react';
import { connect } from 'react-redux';
import { useParams } from 'react-router-dom';
// components

import { Page404 } from 'pages/errors';
import { LoadingScreen } from 'components/ui';
import { SaveButton } from 'components/buttons';
import { UnsavedChangesPrompt } from 'components/navigation';
// rows
import EditStaffRow from './rows/EditStaffRow';
import PositionRow from './rows/PositionRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { setTitle } from 'functions/utils';
import { filterUpdates, onInputChange } from 'functions/events';
import { showError } from 'functions/notifications';
// state
import { getStaff as getStaffOp, updateStaff as updateStaffOp, createAuth as createAuthOp } from 'store/base/staff/operations';

const StaffDetailPage = (props) => {
  const { staff: staffList, getStaff, updateStaff, createAuth, loading, login } = props;
  const { id } = useParams();
  const staffId = parseInt(id, 10);

  const [updates, setUpdates] = useState({});

  // cache the result for performance
  const currentStaff = useMemo(() => {
    return staffList.find(s => s.admin_id === staffId);
  }, [staffList, staffId]);

  useEffect(() => {
    setTitle('Staff Account');
    if (staffList.length === 0) {
      showError(getStaff());
    }
  }, [staffList, getStaff]);

  useEffect(() => {
    if (currentStaff) {
      // if staff changed, clear updates (roughly equivalent to componentDidUpdate check)
      // actually, converting class to func, we just need to ensure updates apply to current staff
      // if ID changes, we probably want to clear updates.
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setUpdates({});
    }
  }, [currentStaff, staffId]);

  const handleUpdates = (newUpdates) => {
    const filtered = filterUpdates(currentStaff, { ...updates, ...newUpdates });
    setUpdates(filtered);
  };

  const handleChange = onInputChange(handleUpdates);

  const save = () => {
    updateStaff(staffId, updates)
      .catch(error => toast.error(error.message));
  };

  const handleCreateAuth = (auth) => {
    createAuth(auth)
      .then(() => toast.info('Position Created'))
      .catch(error => toast.error(error.message));
  };

  if (loading && !currentStaff) return <LoadingScreen />;
  if (!currentStaff && !loading && staffList.length > 0) return <Page404 />; // Only 404 if loaded and not found
  if (!currentStaff) return <LoadingScreen />; // Initial load

  const staffDisplay = { ...currentStaff, ...updates };
  const isUpdated = Object.keys(updates).length > 0;

  return (
    <div id='StaffDetailPage'>
      <UnsavedChangesPrompt
        when={isUpdated}
        message="You have unsaved changes. Are you sure you want to leave?"
      />

      <p className='title'>Account Information</p>

      <EditStaffRow
        {...staffDisplay}
        onChange={handleChange}
      />

      <SaveButton show={isUpdated} onClick={save} />

      <p className='title'>Positions</p>

      <CreatePositionRow
        adminId={staffDisplay.admin_id}
        showCreateButton
        login={login}
        onCreate={handleCreateAuth}
        isAdmin={isAdmin(login.code)}
      />

      {staffDisplay.positions.map(
        (position, index) => <PositionRow key={index} {...position} login={login} />)
      }
    </div>
  );
};

const mapStateToProps = ({ base, login }) => ({
  ...base.staff,
  login: login.current_login
})

const mapDispatchToProps = {
  getStaff: getStaffOp, updateStaff: updateStaffOp, createAuth: createAuthOp
}

export default connect(
  mapStateToProps, mapDispatchToProps
)(StaffDetailPage);
