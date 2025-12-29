import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import { InlineSync } from 'components/ui/loading';
import { ButtonBar, Table, Callout, FontAwesome } from 'components/ui';
// modals
import NewParentModal from './NewParentModal';
// functions
import { toast } from 'react-toastify';
import { setTitle, canDownload } from 'functions/utils';
// state
import { getParents } from 'store/base/parents/operations';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const ParentsPage = () => {
  const dispatch = useDispatch();
  const { parents, loading } = useSelector(state => state.base.parents);
  const [showModal, setShowModal] = useState(false);

  // load the contents if we do not have any
  useEffect(() => {
    setTitle('Parents');
    if (parents.length === 0) {
      dispatch(getParents())
        .catch(error => toast.error(error.message));
    }
  }, [dispatch, parents.length]);

  const refreshParents = () => {
    dispatch(getParents())
      .catch(error => toast.error(error.message));
  };

  const toggle = () => setShowModal(prev => !prev);

  const toCSV = () => {
    // convert to escaped, multiline string
    const getChildrenString = (parent) => {
      return JSON.stringify(parent.children.map(
        child => `${child.first} ${child.last}`).join('; ')
      );
    }
    // CSV headers
    const headers = [
      'Parent ID', 'First', 'Last', 'Username', 'Father Cell', 'Mother Cell', 'E-mail',
      'Address', 'City', 'State', 'Zip', 'Country', 'Children'
    ];
    // generate rows
    const rows = parents.map(parent => [
      parent.admin_id, parent.first, parent.last, parent.username, parent.cell, parent.mother_cell,
      parent.email, (parent.admin_address1 + parent.admin_address2), parent.admin_city, parent.admin_state,
      parent.admin_postal, parent.admin_country, getChildrenString(parent)
    ]);
    return dataToCSV(headers, rows);
  }

  const basePath = '/bm/parents';

  let columns = [
    {
      Header: 'Parent ID', accessor: 'admin_id',
      Cell: props => <Link to={`${basePath}/${props.original.admin_id}`}>{props.value}</Link>
    },
    {
      Header: 'First Name', accessor: 'first',
      Cell: props => <Link to={`${basePath}/${props.original.admin_id}`}>{props.value}</Link>
    },
    {
      Header: 'Last Name', accessor: 'last',
      Cell: props => <Link to={`${basePath}/${props.original.admin_id}`}>{props.value}</Link>
    },
    {
      Header: 'Username', accessor: 'username',
      Cell: props => <Link to={`${basePath}/${props.original.admin_id}`}>{props.value}</Link>
    },
    { Header: 'Cell Phone', accessor: 'cell' },
    { Header: 'E-mail Address', accessor: 'email' },
    { Header: 'Children', id: 'children', accessor: parent => parent.children.length },
  ];

  return (
    <div id='ParentsPage' className='full-height'>
      <Callout title='View Parent Accounts'>
        <p>
          Parents are any account with direct access to a soldier via the Parent Portal.
          Please note that for security reasons you cannot edit their accounts or view their passwords once they are created.
        </p>
        <p><strong>To add / remove children please select the First or Last name and have their Serial Number ready.</strong></p>
      </Callout>

      <ButtonBar style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
        <Button onClick={toggle} className='btn btn-primary'>
          <FontAwesome icon='plus' /> Create Parent Account
        </Button>
        <Button color='primary' onClick={refreshParents}>
          <InlineSync loading={loading} /> Refresh
        </Button>
        {canDownload(parents) &&
          <CSVLink
            data={toCSV()}
            filename={"parents.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Parents (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={parents}
        columns={columns}
        loading={loading && !parents.length}
        pageId='ParentsPage'
        defaultSorted={[{ id: "first", desc: false }, { id: "last", desc: false }]}
      />

      <NewParentModal
        isOpen={showModal}
        toggle={toggle}
      />
    </div>
  )
}

export default ParentsPage;
