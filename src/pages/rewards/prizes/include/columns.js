import React from 'react';
import { DEFAULT_PRIZE } from 'components/constants';
// components
import { Stock } from './components';
import { Link } from 'react-router-dom';
import { Toggle } from 'components/inputs';
import { Number, DateDisplay, StorePrize } from 'components/ui';

const yesNoFilter = ( filter, row ) => {
  if ( filter.value === 'all' ) return true;
  if ( filter.value === 'yes') return !!row[filter.id];
  if ( filter.value === 'no') return !row[filter.id];
}

const yesNoFilterRender = ( onOff = false ) => ({ filter, onChange }) => (
  <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
    onChange={event => onChange(event.target.value)}>
    <option value="all">Show All</option>
    <option value="yes">{ onOff ? 'On' : 'Yes' }</option>
    <option value="no">{ onOff ? 'Off' : 'No' }</option>
  </select>
);

export function getColumns( editPicture, path, updateToggle, admin ) {
  return [
    {
      Header: 'Picture',  accessor: 'image',
      className: 'profile-picture', width: 85, sortable: false,
      Cell: props => 
        <StorePrize src={ props.value } className='inline-profile' 
          onClick={ admin? undefined : editPicture( props.original.prize_id ) }/>,
      Filter: ({ filter, onChange }) =>
        <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
          onChange={event => onChange(event.target.value)}>
          <option value="all">Show All</option>
          <option value="yes">Has Picture</option>
          <option value="no">No Picture</option>
        </select>,
      filterMethod: ( filter, row ) => {
        if ( filter.value === 'all' ) return true;
        if ( filter.value === 'yes') return row[filter.id] !== DEFAULT_PRIZE;
        if ( filter.value === 'no') return row[filter.id] === DEFAULT_PRIZE;
      }
    },

    { Header: 'Prize Name', accessor: 'prize_name',
      Cell: props => {
        if ( admin )
          return props.value;
        return <Link to={`${path}/${props.original.prize_id}`}>{props.value}</Link> 
      }
    },
  
    { Header: 'Miles', accessor: 'points', Cell: props => <Number value={props.value}/> },

    { Header: 'In Stock', accessor: 'prize_count', Cell: props => <Stock value={props.value}/> },
  
    { Header: 'Status', accessor: 'is_active', 
      Filter: yesNoFilterRender( true ), filterMethod: yesNoFilter,
      Cell: ({ value, original }) => 
        <Toggle
          className='danger' 
          disabled={ admin }
          checked={ !!value }
          onChange={ updateToggle( 'is_active', original.prize_id ) } />,
    },

    { Header: 'One Per Soldier', accessor: 'one_per_user',
      Filter: yesNoFilterRender( false ), filterMethod: yesNoFilter,
      Cell: ({ value, original }) => 
        <Toggle
          on='yes' off='no'
          disabled={ admin } 
          checked={ !!value } 
          onChange={ updateToggle( 'one_per_user', original.prize_id ) } />,
    },
  
    { Header: 'Last Updated', accessor: 'modified', filterable: false,
      Cell: props => <DateDisplay value={props.value} fromNow />, },
  ];
}
