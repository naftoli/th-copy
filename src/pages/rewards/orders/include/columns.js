import React from 'react';
// components
// import { Toggle } from 'components/inputs';
import { Number, DateDisplay } from 'components/ui';

export function getColumns( bc ) {
  const columns = [
    { Header: 'Date', accessor: 'modified', filterable: false,
      Cell: ({ value }) => <DateDisplay value={ value } format='l LT' />
    },
  
    { Header: 'First Name', accessor: 'first' },
    { Header: 'Last Name', accessor: 'last' },
    { Header: 'Prize', accessor: 'prize_name' },

    { Header: 'Miles', accessor: 'total', Cell: props => <Number value={ props.value }/> },
    { Header: 'Qty', accessor: 'quantity', Cell: props => <Number value={props.value}/> },
  ];

  if ( bc ) {
    columns.push(
      { Header: 'Platoon', accessor: 'platoon' },
    )
  }

  return columns;
}
