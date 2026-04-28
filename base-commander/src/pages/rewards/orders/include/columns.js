import React from 'react';
// components
import { NumberDisplay, DateDisplay } from 'components/ui';

export function getColumns( bc ) {
  const columns = [
    { Header: 'Prize', accessor: 'prize_name' },
    { Header: 'First Name', accessor: 'first' },
    { Header: 'Last Name', accessor: 'last' },

    { Header: 'Qty', accessor: 'quantity', Cell: props => <NumberDisplay value={props.value}/> },
    { Header: 'Total Miles', accessor: 'total', Cell: props => <NumberDisplay value={ props.value * -1 }/> },
  ];

  if ( bc ) {
    columns.push(
      // { Header: 'Platoon', accessor: 'platoon' },
      { Header: 'Grade', accessor: 'class_grade' },
      { Header: 'Sub', accessor: 'class_sub' },
    )
  }

  columns.push(
    { Header: 'Date', accessor: 'created', filterable: false,
      Cell: ({ value }) => <DateDisplay value={ value } format='l LT' />
    },
  )

  // show admin id if school is 269 (as last column)
  if ( bc ) {
    columns.push(
      { Header: 'Admin ID', accessor: 'admin_id' },
    )
  }

  return columns;
}
