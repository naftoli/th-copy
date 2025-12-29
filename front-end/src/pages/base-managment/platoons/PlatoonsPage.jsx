import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import NewPlatoonModal from './newPlatoonModal';
import {
  InlineSync, ButtonBar, Table, Callout, FontAwesome, NumberDisplay
} from 'components/ui';
// functions
import { isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
import { getPlatoons, createPlatoon } from 'store/base/platoons/operations';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';
import { LEGACY_URL } from 'components/constants';

export const PlatoonsPage = () => {
  const dispatch = useDispatch();
  const { platoons, loading } = useSelector(state => state.base.platoons);
  const login = useSelector(state => state.login.current_login);

  const [showModal, setShowModal] = useState(false);

  // load the contents if we do not have any
  useEffect(() => {
    setTitle('View/Edit Platoons');
    dispatch(getPlatoons());
  }, [dispatch]);

  const toggle = () => setShowModal(prev => !prev);
  const refreshPlatoons = () => dispatch(getPlatoons());
  const handleCreatePlatoon = (data) => dispatch(createPlatoon(data));

  const toCSV = () => {
    const headers = ['Grade', 'Subject', 'Teacher', 'Cell Phone', 'E-mail', '# of Soldiers', '# of Staff', 'Base'];
    const rows = platoons.map(platoon => [
      platoon.class_grade, platoon.class_sub, platoon.teacher, platoon.cell,
      platoon.email, platoon.soldier_count, platoon.staff_count, platoon.school_name
    ]);
    return dataToCSV(headers, rows);
  }

  const basePath = '/bm/platoons';

  let columns = [
    {
      Header: 'Grade', accessor: 'class_grade',
      Cell: props => <Link to={`${basePath}/${props.original.class_id}`}>{props.value}</Link>
    },
    {
      Header: 'Class', accessor: 'class_sub',
      Cell: props => <Link to={`${basePath}/${props.original.class_id}`}>{props.value}</Link>
    },
    {
      Header: 'Teacher', accessor: 'teacher',
      Cell: props => <Link to={`${basePath}/${props.original.class_id}`}>{props.value}</Link>
    },
    { Header: 'Cell Phone', accessor: 'cell' },
    { Header: 'E-mail Address', accessor: 'email' },
    { Header: 'Soldiers', accessor: 'soldier_count' },
    { Header: 'Staff', accessor: 'staff_count' },
    {
      Header: 'Miles Balance', accessor: 'miles_balance',
      Cell: props => <NumberDisplay value={props.value} />
    },
  ];
  if (isAdmin(login.code)) {
    columns.push({ Header: 'Base', accessor: 'school_name' });
  }

  return (
    <div id='PlatoonsPage'>
      <Callout title="Platoons">
        <p><strong>To connect a Staff member to a Platoon go to the edit page by clicking on the Grade, Sub or Teacher.</strong></p>
      </Callout>
      <ButtonBar style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
        <Button color="primary" onClick={toggle}>
          <FontAwesome icon='plus' /> Add Platoon
        </Button>
        <Link to={`${basePath}/transition`} className="btn btn-primary" role="button">
          <FontAwesome icon='users' /> Platoon Transition
        </Link>
        <a href={`${LEGACY_URL}/teacher_letter.php`}
          className="btn btn-primary" target='_blank' role="button">
          <FontAwesome icon='mail-bulk' /> Print Teacher Letters
        </a>
        <Button color="primary" onClick={refreshPlatoons}>
          <InlineSync loading={loading} /> Refresh
        </Button>
        {canDownload(platoons) &&
          <CSVLink
            data={toCSV()}
            filename={"platoons.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Platoons (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={platoons}
        columns={columns}
        loading={loading && !platoons.length}
        pageId='PlatoonsPage' />

      <NewPlatoonModal
        login={login}
        isOpen={showModal}
        toggle={toggle}
        refresh={refreshPlatoons}
        onSubmit={handleCreatePlatoon} />

    </div>
  )
}

export default PlatoonsPage;
