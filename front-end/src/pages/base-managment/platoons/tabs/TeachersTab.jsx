import React from 'react';
// components
import { TabPane } from 'reactstrap';
import { NewStaffRow, StaffRow } from 'components/rows';
// functions
import { toast } from 'react-toastify';

export const TeachersTab = ({
  platoon, tabId,
  removeAuth, refresh, createAuth
}) => {

  //  disconnect and connect staff
  const disconnect = (admin_id) => {
    const { class_id: id } = platoon;
    removeAuth({ admin_id, id, auth: 'class' })
      .then(refresh)
      .catch(error => { toast.error(error.message) });
  }

  // create the connection
  const connect = ({ email, username }) => {
    const { class_id: id } = platoon;
    createAuth({ email, username, id, auth: 'class' })
      .then(refresh)
      .catch(error => { toast.error(error.message) });
  }

  return (
    <TabPane tabId={tabId} id='TeachersTab' >

      <NewStaffRow onSubmit={connect} />

      <div id='teachers'>
        {platoon.staff.map((staff, index) =>
          <StaffRow key={index} disconnect={disconnect} {...staff} />
        )}
      </div>

    </TabPane>
  );
}
