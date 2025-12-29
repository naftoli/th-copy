import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import RegistrationModal from './RegistrationModal';
import { Row, Col, Button } from 'reactstrap';
import { ButtonBar, SelectTable, Callout, InlineSync, FontAwesome } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import { getPaymentProfiles } from 'store/payments/operations';
import { getSoldiers, registerSoldiers } from 'store/base/soldiers/registration/operations';
// styles
import './RegistrationPage.scss';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const RegistrationPage = ({
  login, soldiers, loading, payments, getSoldiers, getPaymentProfiles, registerSoldiers
}) => {

  const [total, setTotal] = useState(0);
  const [selection, setSelection] = useState([]);
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    setTitle('Soldier Registration');
    // if the soldiers are empty then get the soldiers
    // if (soldiers.length === 0)
    //   handleGetSoldiers();
    // Always fetch soldiers on mount to ensure fresh data? Or rely on length check?
    // Original code checked length === 0. Keeping that logic.
    if (soldiers.length === 0) {
      handleGetSoldiers();
    }
    // get the current payment profiles
    getPaymentProfiles();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // Run once on mount

  const toCSV = () => {
    const headers = [
      'First Name', 'Last Name', 'Serial Number', 'Registration Fee', 'Platoon'
    ];
    const rows = soldiers.map(soldier => [
      soldier.first, soldier.last, soldier.user_serial, soldier.fee,
      soldier.platoon ? `="${soldier.platoon}"` : ''
    ]);
    return dataToCSV(headers, rows);
  }

  // close the modal
  const closeModal = () => setShowModal(false);

  // open the modal or just register the soldiers
  const payAndRegister = () => {
    setShowModal(true);
  }

  // get the id
  const getId = row => row.user_id;

  // toggle a row in the table
  const toggleRow = (newSelection, { fee }) => {
    // Instead of incremental update, recalculate total from scratch for safety
    // Assuming soldiers list is available and contains all selectable soldiers

    // However, recreating total from newSelection requires finding all selected soldiers.
    // If soldiers array is large, this is O(N*M).
    // Incremental update is O(1).
    // Let's stick to the incremental update logic but implemented cleanly with hooks?
    // Or optimized recalculation.
    // Given the props.soldiers array, we can filter. `soldiers` shouldn't be massive (hundreds?).

    /* Optimized Recalculation: */
    // const newTotal = soldiers
    //   .filter(s => newSelection.includes(s.user_id))
    //   .reduce((sum, s) => sum + parseInt(s.fee, 10), 0);
    // setTotal(newTotal);

    /* Original Incremental Logic (Matches user behavior if fees are consistent): */
    // if ( selection.length > newSelection.length ) fee = -Math.abs(fee);
    // const newTotal = parseInt(total, 10) + parseInt(fee, 10);

    // I'll update selection and total together.

    let feeVal = parseInt(fee, 10);
    if (selection.length > newSelection.length)
      feeVal = -Math.abs(feeVal);

    // Check if we can just recalculate to be safe.
    // Let's trust the incremental logic for now as it was in the original, unless it was buggy.
    // Actually, recalculating is safer against sync issues.

    // Let's use the recalculation approach for robustness.
    const newTotal = soldiers
      .filter(s => newSelection.includes(s.user_id))
      .reduce((sum, s) => sum + (parseInt(s.fee, 10) || 0), 0); // Handle potential NaN

    setSelection(newSelection);
    setTotal(newTotal);
  };

  // toggle all the rows in the table
  const toggleAll = (newSelection, currentRecords) => {
    let newTotal = 0;
    if (currentRecords.length > 0)
      currentRecords.forEach(item => { newTotal += parseInt(item._original.fee, 10); });

    setSelection(newSelection);
    setTotal(newTotal);
  };

  // get the soldiers
  const handleGetSoldiers = () => {
    getSoldiers()
      .catch(e => toast.error(e.message));
  }

  // register the soldiers
  const handleRegisterSoldiers = (payment = false) => {
    setShowModal(false);
    // make sure there is more then one soldier
    if (selection.length === 0) {
      return toast.error('Cannot Register 0 Soldiers.');
    }
    // register the soldiers
    registerSoldiers(selection, payment, total)
      .then(handleGetSoldiers)
      .catch(e => toast.error(e.message));
  }

  // define table columns
  let columns = [
    {
      Header: 'First Name', accessor: 'first',
      Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link>
    },
    {
      Header: 'Last Name', accessor: 'last',
      Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link>
    },
    {
      Header: 'Serial Number', accessor: 'user_serial',
      Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`} tabIndex={-1}>{props.value}</Link>
    },
    { Header: 'Registration Fee', accessor: 'fee' },
    { Header: 'Platoon', accessor: 'platoon' },
  ];
  // add base column for HQ
  if (isAdmin(login.code)) {
    columns.push({ Header: 'Base', accessor: 'school_name' });
  }

  console.log(payments);

  return (
    <div id='RegistrationPage'>
      <Callout title='Soldier Registration'>
        All unregistered soldiers are displayed below, along with the cost to register.<br />
        Please select the Soldiers you are registering.
      </Callout>

      <ButtonBar>
        <Button
          color='primary'
          onClick={payAndRegister}
          disabled={selection.length === 0}>
          <FontAwesome icon='dollar-sign' /> Pay and Register
        </Button>

        <Button color='primary' onClick={handleGetSoldiers}>
          <InlineSync loading={loading} /> Refresh
        </Button>

        {canDownload(soldiers) &&
          <CSVLink
            data={toCSV()}
            filename={"not_registered_soldiers.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Page (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Row>
        <Col xs='12' sm='6'>
          <h2 id='total'>
            Total: ${total.toLocaleString(navigator.language)}
          </h2>
        </Col>
        <Col xs='12' sm='6'>
          <h2 id='total'>
            Soldiers: {selection.length.toLocaleString(navigator.language)}
          </h2>
        </Col>
      </Row>

      <SelectTable
        pageId='RegistrationPage'
        data={soldiers} columns={columns}
        loading={loading && !soldiers.length}

        getId={getId}
        selection={selection}
        maxSelectionSize={soldiers.length}
        toggleRow={toggleRow}
        toggleAll={toggleAll} />

      <RegistrationModal
        isOpen={showModal}
        payments={payments}
        toggle={closeModal}
        onSubmit={handleRegisterSoldiers} />
    </div>
  )
}

export default connect(({ login, base, payments }) => ({
  login: login.current_login,
  soldiers: base.soldiers.registration_soldiers,
  loading: base.soldiers.loading,
  payments: payments.payments
}), {
  getSoldiers, registerSoldiers, getPaymentProfiles
})(RegistrationPage);
