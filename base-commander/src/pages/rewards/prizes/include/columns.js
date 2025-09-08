import React from 'react';
import { DEFAULT_PRIZE } from 'components/constants';
// components
import { Stock } from './components';
import { Toggle } from 'components/inputs';
import { NumberDisplay, StorePrize } from 'components/ui';
import { Input } from 'reactstrap';
// functions
import { yesNoFilter, yesNoFilterRender } from 'functions/tables';

export function getColumns({
  editPicture, editPrize, updateToggle, isTemplate = false, updateNumPerUser,
  toggleRowFromCell
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
      accessor: 'points', Cell: props => <NumberDisplay value={props.value}/> },
  ];

  // add the toggles
  if ( !isTemplate ) {
    columns.push(
      { Header: 'In Stock', accessor: 'prize_count', 
        Cell: ({ value }) => isTemplate ? <NumberDisplay value={value}/> : <Stock value={ value }/> },

      { Header: 'Status', accessor: 'is_active', 
        Filter: dropdown, filterMethod: yesNoFilter,
        Cell: ({ value, original }) => 
          <Toggle
            disabled={ !original.editable }
            checked={ !!value }
            onChange={ updateToggle( 'is_active', original.prize_id ) } />,
      },
      
      { Header: 'Max Per Soldier', accessor: 'num_per_user',
        Cell: props => (
          <div style={{ display: 'flex', justifyContent: 'center' }}>
            <Input
              type="number"
              name="num_per_user"
              min="0"
              value={props.value || ''}
              onChange={e => updateNumPerUser(props.original.prize_id, e.target.value)}
              style={{ width: 60 }}
              disabled={!props.original.editable}
              placeholder="0"
              onBlur={e => {
                if (e.target.value === '') {
                  updateNumPerUser(props.original.prize_id, '0');
                }
              }}
            />
          </div>
        )
      },

      { Header: 'Discount', accessor: 'discount_amount',
        Cell: props => {
          const { discount_amount, discount_type } = props.original;
          const content = (!discount_amount || discount_amount <= 0)
            ? '-'
            : (discount_type === 'percent' ? `${discount_amount}% off` : `${discount_amount} miles off`);
          return (
            <span style={{ cursor: toggleRowFromCell ? 'pointer' : 'default' }}
              onClick={ toggleRowFromCell ? () => toggleRowFromCell(props.original) : undefined }>
              {content}
            </span>
          );
        }
      },

      { Header: 'Final Price', accessor: 'points',
        Cell: props => {
          const { points, discount_amount, discount_type } = props.original;
          if (!discount_amount || discount_amount <= 0) {
            return <NumberDisplay value={points}/>;
          }
          
          let finalPrice = points;
          if (discount_type === 'percent') {
            finalPrice = points * (1 - discount_amount / 100);
            // Round up if there's a decimal part
            finalPrice = Math.ceil(finalPrice);
          } else {
            finalPrice = Math.max(0, points - discount_amount);
          }
          
          return (
            <div>
              <NumberDisplay value={finalPrice} style={{ color: '#28a745', fontWeight: 'bold' }}/>
              <br />
              <small style={{ color: '#6c757d', textDecoration: 'line-through' }}>
                <NumberDisplay value={points}/>
              </small>
            </div>
          );
        }
      },
    );
  }
  
  if ( isTemplate ) {
    columns.push(
      { Header: 'Max Per Soldier', id: 'num_per_user', accessor: ({ num_per_user }) => num_per_user || 0 }
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
