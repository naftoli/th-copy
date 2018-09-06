import React from 'react';
import { DEFAULT_PRIZE } from 'components/constants';
// components
import { Stock } from './components';
import { Toggle } from 'components/inputs';
import { Number, StorePrize } from 'components/ui';

const yesNoFilter = ( filter, row ) => {
  if ( filter.value === 'all' ) return true;
  if ( filter.value === 'yes') return !!row[filter.id];
  if ( filter.value === 'no') return !row[filter.id];
}

const yesNoFilterRender = ({ filter, onChange }) => (
  <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
    onChange={event => onChange(event.target.value)}>
    <option value="all">Show All</option>
    <option value="yes">On</option>
    <option value="no">Off</option>
  </select>
);

export function getColumns({
  editPicture, editPrize, updateToggle, isTemplate = false
}) {
  const columns = [
    {
      Header: 'Picture',  accessor: 'image',
      className: 'profile-picture', width: 85, sortable: false,
      Cell: ({ value, original }) => 
        <StorePrize src={ value } className='inline-profile' 
          onClick={ original.editable ? editPicture( original.prize_id ) : undefined }/>,
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
      Cell: ({ value, original }) => {
        if ( !original.editable ) return value;
        return <a tabIndex={ 0 } onClick={ editPrize( original ) }>{ value }</a>
      }
    },
  
    { Header: isTemplate ? 'Default Miles' : 'Miles', 
      accessor: 'points', Cell: props => <Number value={props.value}/> },

    { Header: isTemplate ? 'Default Stock' : 'In Stock', accessor: 'prize_count', 
      Cell: ({ value }) => isTemplate ? <Number value={value}/> : <Stock value={ value }/> },
  ];

  // add the toggles
  if ( !isTemplate ) {
    columns.push(
      { Header: 'Status', accessor: 'is_active', 
        Filter: yesNoFilterRender, filterMethod: yesNoFilter,
        Cell: ({ value, original }) => 
          <Toggle
            disabled={ !original.editable }
            checked={ !!value }
            onChange={ updateToggle( 'is_active', original.prize_id ) } />,
      },
      { Header: 'One Per Soldier', accessor: 'one_per_user',
        Filter: yesNoFilterRender, filterMethod: yesNoFilter,
        Cell: ({ value, original }) => 
          <Toggle
            disabled={ !original.editable } 
            checked={ !!value } 
            onChange={ updateToggle( 'one_per_user', original.prize_id ) } />,
      },
    );
  }
  
  if ( isTemplate ) {
    columns.push(
      { Header: 'Default Status', id: 'status', accessor: ({ is_active }) => is_active ? 'Active' : 'Disabled' },
      { Header: 'One Per Soldier', id: 'one_per_user', accessor: ({ one_per_user }) => one_per_user ? 'Yes' : 'No' }
    )
  }

  // add the number of platoons
  if ( !isTemplate ) {
    columns.push(
      { Header: 'Platoons', id: 'platoons', 
        accessor: ({ platoons }) => platoons.length > 0 ? `${platoons.length}` : 'All' },
    );
  }

  return columns;
}
