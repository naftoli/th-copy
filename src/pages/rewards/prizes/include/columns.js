import React from 'react';
import { DEFAULT_PRIZE } from 'components/constants';
// components
import { Stock } from './components';
import { Toggle } from 'components/inputs';
import { Number, StorePrize } from 'components/ui';
// functions
import { yesNoFilter, yesNoFilterRender } from 'functions/tables';

export function getColumns({
  editPicture, editPrize, updateToggle, isTemplate = false
}) {

  const dropdown = yesNoFilterRender('On', 'Off');

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
  ];

  // add the toggles
  if ( !isTemplate ) {
    columns.push(
      { Header: 'In Stock', accessor: 'prize_count', 
        Cell: ({ value }) => isTemplate ? <Number value={value}/> : <Stock value={ value }/> },

      { Header: 'Status', accessor: 'is_active', 
        Filter: dropdown, filterMethod: yesNoFilter,
        Cell: ({ value, original }) => 
          <Toggle
            disabled={ !original.editable }
            checked={ !!value }
            onChange={ updateToggle( 'is_active', original.prize_id ) } />,
      },
      
      { Header: 'One Per Soldier', accessor: 'one_per_user',
        Filter: dropdown, filterMethod: yesNoFilter,
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
