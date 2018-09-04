import React from 'react';
import { DEFAULT_PRIZE } from 'components/constants';
// components
import { Stock } from './components';
import { Link } from 'react-router-dom';
import { Toggle } from 'components/inputs';
import { Number, Date, StorePrize } from 'components/ui';

export function getColumns( editPicture, path, updateToggle, admin ) {
  return [
    {
      Header: 'Picture',  accessor: 'image',
      className: 'profile-picture', width: 85, sortable: false,
      Cell: props => <StorePrize src={ props.value } className='inline-profile' 
                        onClick={ editPicture( props.original.prize_id ) }/>,
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
      Cell: props => <Link to={`${path}/${props.original.prize_id}`}>{props.value}</Link> },
  
    { Header: 'Miles', accessor: 'miles', Cell: props => <Number value={props.value}/> },

    { Header: 'In Stock', accessor: 'stock', Cell: props => <Stock value={props.value}/> },
  
    { Header: 'Active', accessor: 'is_active', 
      Cell: ({ value, original }) => 
        <Toggle
          className='danger' 
          disabled={ admin }
          checked={ !!value }
          onChange={ updateToggle( 'is_active', original.prize_id ) } />, 
    },

    { Header: 'One Per Soldier', accessor: 'one_per_user', 
      Cell: ({ value, original }) => 
        <Toggle
          on='yes' off='no'
          disabled={ admin } 
          checked={ !!value } 
          onChange={ updateToggle( 'one_per_user', original.prize_id ) } />,
    },
  
    { Header: 'Last Updated', accessor: 'modified', Cell: props => <Date value={props.value}/>, filterable: false },
  ];
}
