import React from 'react';
import PropTypes from 'prop-types';
// components
import { TabPane } from 'reactstrap';
import { StaffRow, NewStaffRow } from '../rows';
// functions
import { toast } from 'react-toastify';

export const StaffTab = ({
  staff,
  tabId,
  refresh,
  schoolId,
  createAuth,
  removeAuth
}) => {

  const disconnect = (admin_id) => {
    const id = schoolId;
    removeAuth({ admin_id, id, auth: 'school' })
      .then(refresh)
      .catch(error => { toast.error(error.message) });
  }

  const connect = ({ email, username }) => {
    const id = schoolId;
    // create the connection
    createAuth({ email, username, id, auth: 'school' })
      .then(refresh)
      .catch(error => { toast.error(error.message) });
  }

  return (
    <TabPane tabId={tabId}>
      <div id='StaffTab'>

        {/* show all the staff and manage them */}
        <NewStaffRow onSubmit={connect} />

        {staff.map((staff, index) =>
          <StaffRow key={index} disconnect={disconnect} {...staff} />
        )}

      </div>
    </TabPane>
  )
}

StaffTab.propTypes = {
  staff: PropTypes.array.isRequired,
  tabId: PropTypes.number.isRequired,
  refresh: PropTypes.func.isRequired,
  schoolId: PropTypes.number.isRequired,
  createAuth: PropTypes.func.isRequired,
  removeAuth: PropTypes.func.isRequired,
};
