import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { ButtonBar, Table, Callout, InlineSync, FontAwesome } from 'components/ui';
import { Button } from 'reactstrap';
// modals
import NewStaffModal from './NewStaffModal';
// functions
import { isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import { getStaff } from 'store/base/staff/operations';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const StaffPage = () => {
  const dispatch = useDispatch();
  const { staff, loading } = useSelector(state => state.base.staff);
  const login = useSelector(state => state.login.current_login);
  const [showModal, setShowModal] = useState(false);

  // load the contents if we do not have any
  useEffect(() => {
    setTitle('Staff');
    dispatch(getStaff());
  }, [dispatch]);

  const refreshStaff = () => dispatch(getStaff());
  const toggle = () => setShowModal(prev => !prev);

  const toCSV = () => {
    let headers, rows
    if (isAdmin(login.code)) {
      headers = [
        'Username', 'First Name', 'Last Name', 'E-mail Address',
        'Cell Phone', 'Position', 'Platoon', 'Base', 'Base ID', 'Staff ID'
      ];
      rows = staff.map(s => [
        s.username, s.first, s.last,
        s.email, s.cell, s.position,
        s.platoon, s.school_name,
        s.school_id, s.admin_id
      ]);
    } else {
      headers = [
        'Username', 'First Name', 'Last Name', 'E-mail Address',
        'Cell Phone', 'Position', 'Platoon'
      ];
      rows = staff.map(s => [
        s.username, s.first, s.last,
        s.email, s.cell, s.position,
        s.platoon
      ]);
    }
    return dataToCSV(headers, rows);
  }

  const basePath = '/bm/staff';

  let columns = [
    {
      Header: 'Username', accessor: 'username',
      Cell: props => <Link to={`${basePath}/${props.original.admin_id}`}>{props.value}</Link>
    },
    {
      Header: 'Password', accessor: 'password',
      Cell: props => <Link to={`${basePath}/${props.original.admin_id}`}>{props.value}</Link>
    },
    { Header: 'First Name', accessor: 'first' },
    { Header: 'Last Name', accessor: 'last' },
    { Header: 'E-mail Address', accessor: 'email' },
    { Header: 'Cell Phone', accessor: 'cell' },
    { Header: 'Position', accessor: 'position' },
    { Header: 'Platoon', accessor: 'platoon' },
  ];

  if (isAdmin(login.code)) {
    columns.push({ Header: 'Base', accessor: 'school_name' })
  }

  return (
    <div id='StaffPage' className='full-height'>
      <Callout title='View / Edit Staff Accounts'>
        <p>Staff accounts are any accounts connected to your base.</p>
      </Callout>

      <ButtonBar>
        <Button onClick={toggle} className='btn btn-primary'>
          <FontAwesome icon='plus' /> Create Staff Account
        </Button>
        <Button color='primary' onClick={refreshStaff}>
          <InlineSync loading={loading} /> Refresh
        </Button>
        {canDownload(staff) &&
          <CSVLink
            data={toCSV()}
            filename={"staff.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Staff (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={staff}
        loading={loading && !staff.length}
        columns={columns}
        pageId='StaffPage' />

      <NewStaffModal
        isOpen={showModal}
        toggle={toggle}
      />
    </div>
  )
}

export default StaffPage;
