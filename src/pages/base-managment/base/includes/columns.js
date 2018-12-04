import React from 'react';

import { Link } from 'react-router-dom';
import { Toggle } from 'components/inputs';
import { BaseLogo, NumberDisplay } from 'components/ui';

import memoize from 'memoize-one';
import { DEFAULT_LOGO } from 'components/constants';
import { yesNoFilter, yesNoFilterRender } from 'functions/tables';

const getColumns = ( path, editPicture, updateToggle ) => {
  // define the table for the page
  let columns = [
    // logo
    { Header: 'Logo',  accessor: 'logo',
      className: 'profile-picture', width: 85, sortable: false,
      Cell: props => <BaseLogo src={ `/schoolLogos/${props.value}` } className='inline-profile' 
                        onClick={ editPicture( props.original.school_id ) }/>,
      Filter: ({ filter, onChange }) =>
        <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
          onChange={event => onChange(event.target.value)}>
          <option value="all">Show All</option>
          <option value="yes">Has Logo</option>
          <option value="no">Default Logo</option>
        </select>,
      filterMethod: ( filter, row ) => {
        if ( filter.value === 'all' ) return true;
        if ( filter.value === 'yes') return row[filter.id] !== DEFAULT_LOGO;
        if ( filter.value === 'no') return row[filter.id] === DEFAULT_LOGO;
      },
    },

    { Header: 'Base Number', accessor: 'school_number',
      Cell: ({ original, value }) => <Link to={`${path}/${original.school_id}`}>{value}</Link> },
    { Header: 'Base Name', accessor: 'school_name',
      Cell: ({ original, value }) => <Link to={`${path}/${original.school_id}`}>{value}</Link> },

    { Header: 'Base City', accessor: 'school_city' },
    { Header: 'Base State', accessor: 'school_state' },
    { Header: 'Base Country', accessor: 'school_country' },

    { Header: 'CTH', accessor: 'chayolei',
      Filter: yesNoFilterRender(), filterMethod: yesNoFilter,
      Cell: ({ value, original }) => 
        <Toggle checked={ !!value } onChange={ updateToggle( 'chayolei', original.school_id ) } />,
    },

    { Header: 'Chidon', accessor: 'chidon',
      Filter: yesNoFilterRender(), filterMethod: yesNoFilter,
      Cell: ({ value, original }) => 
        <Toggle checked={ !!value } onChange={ updateToggle( 'chidon', original.school_id ) } />,
    },

    { Header: 'WWTC', accessor: 'tehillim',
      Filter: yesNoFilterRender(), filterMethod: yesNoFilter,
      Cell: ({ original }) => <span>{ original.tehillim ? 'Yes' : 'No' }</span>
    },
    { Header: 'Tanya', accessor: 'tanya', 
      Filter: yesNoFilterRender(), filterMethod: yesNoFilter,
      Cell: ({ original }) => <span>{ original.tanya ? 'Yes' : 'No' }</span>
    },

    { Header: 'Soldiers', accessor: 'soldier_count', 
      Cell: props => <NumberDisplay value={props.value}/> },
  ];

  return columns;
};

export default memoize( getColumns );
