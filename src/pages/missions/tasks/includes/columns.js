import React from 'react';

import { Link } from 'react-router-dom';

import { isAdmin } from 'functions/login';
import { yesNoFilter, yesNoFilterRender } from 'functions/tables';

export default ( login, edit ) => {
  // define the table for the page
  let columns = [
    {
      Header: 'Grid Id',  accessor: 'grid_id',
      Cell: ({ value, original }) =>
        <a tabIndex={ 0 } onClick={ edit( original ) }>{ value }</a>
    },
    { Header: 'Campaign',  accessor: 'subject_name' },
    { Header: 'Title',  accessor: 'short_name' },
    { Header: 'Miles',  accessor: 'points' },
    {
      Header: 'Mission Sheets',     accessor: 'mission_marking',
      Filter: yesNoFilterRender(),  filterMethod: yesNoFilter,
      Cell: ({ value }) => <span>{ value ? 'Yes' : 'No'}</span>
    },
    {
      Header: 'Grid Marking',       accessor: 'grid_marking',
      Filter: yesNoFilterRender(),  filterMethod: yesNoFilter,
      Cell: ({ value }) => <span>{ value ? 'Yes' : 'No'}</span>
    },
    { Header: 'Label',  accessor: 'label_name' }
  ];

  // * Institution columns
  if ( isAdmin( login.code ) ) {
    columns.push({
      Header: "Base", accessor: 'school_name',
      Cell: props => <Link to={`/bm/base/${props.original.school_id}`} tabIndex={ -1 }>{props.value}</Link>,
    });
  }

  return columns;
}
