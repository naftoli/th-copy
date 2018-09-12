import React from 'react';
// components
// import { Toggle } from 'components/inputs';
import { Number, DateDisplay } from 'components/ui';

export function getColumns( bc ) {
  const columns = [
    { Header: 'Prize', accessor: 'prize_name' },
    { Header: 'First Name', accessor: 'first' },
    { Header: 'Last Name', accessor: 'last' },

    { Header: 'Qty', accessor: 'quantity', Cell: props => <Number value={props.value}/> },
    { Header: 'Total Miles', accessor: 'total', Cell: props => <Number value={ props.value * -1 }/> },
  ];

  if ( bc ) {
    columns.push(
      { Header: 'Platoon', accessor: 'platoon' },
    )
  }

  columns.push(
    { Header: 'Date', accessor: 'created', filterable: false,
      Cell: ({ value }) => <DateDisplay value={ value } format='l LT' />
    },
  )

  return columns;
}
