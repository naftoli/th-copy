import React from 'react';
import { Link } from 'react-router-dom';
// components
import { SelectTable, ButtonBar, FontAwesome } from 'components/ui';
import { Button } from 'reactstrap';

const Step2 = ({ soldiers, loading, selection, discharge, updateSelection }) => {

  const columns = [
    {
      Header: "First Name", accessor: 'first',
      // Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`}>{props.value}</Link>,
    },
    {
      Header: "Last Name", accessor: 'last',
      // Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`}>{props.value}</Link>,
    },
    {
      Header: "Serial Number", accessor: 'user_serial',
      Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`}>{props.value}</Link>,
    },
    {
      Header: "DOB", accessor: 'dob',
      Cell: props => <Link to={`/bm/soldiers/${props.original.user_id}`}>{props.value}</Link>,
    },
    { Header: 'Transitioning To', accessor: 'transition' },
  ];

  const getId = item => item.user_id;
  const toggle = (selection) => updateSelection(selection);

  return (
    <div id='step-2'>
      <p className="title">Step 2: Select Soldiers</p>
      <SelectTable
        pageId='RegistrationPage'
        columns={columns}
        loading={loading}
        data={loading ? [] : soldiers}

        getId={getId}
        selection={selection}
        maxSelectionSize={soldiers.length}
        toggleRow={toggle}
        toggleAll={toggle} />

      <ButtonBar>
        <Button color='danger' onClick={discharge}>
          <FontAwesome icon="trash-alt" />{' '}
          Remove Soldiers from Base
        </Button>
      </ButtonBar>
    </div>
  );
}

export default Step2;
