import React from 'react';

import { Link } from 'react-router-dom';
import { Toggle } from 'components/inputs';
import { ProfilePicture, DateDisplay } from 'components/ui';

import { isBC, isAdmin } from 'functions/login';
import { DEFAULT_PROFILE } from 'components/constants';
import { yesNoFilter, yesNoFilterRender } from 'functions/tables';

export default ( login, editPicture, updateToggle ) => {
  // define the table for the page
  let columns = [
    {
      Header: 'Profile',  accessor: 'profilePicture',
      className: 'profile-picture', width: 85, sortable: false,
      Cell: props => <ProfilePicture src={ props.value } className='inline-profile' 
                        onClick={ editPicture( props.original.user_id ) }/>,
      Filter: ({ filter, onChange }) =>
        <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
          onChange={event => onChange(event.target.value)}>
          <option value="all">Show All</option>
          <option value="yes">Has Profile</option>
          <option value="no">No Profile</option>
        </select>,
      filterMethod: ( filter, row ) => {
        if ( filter.value === 'all' ) return true;
        if ( filter.value === 'yes') return row[filter.id] !== DEFAULT_PROFILE;
        if ( filter.value === 'no') return row[filter.id] === DEFAULT_PROFILE;
      },
    }, {
      Header: "First Name", accessor: 'first',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`} tabIndex={ -1 } >{props.value}</Link>,
    }, {
      Header: "Last Name", accessor: 'last',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`} tabIndex={ -1 } >{props.value}</Link>,
    }, {
      Header: "Serial Number", accessor: 'user_serial',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    }, {
      Header: 'Date Of Birth', accessor: 'dob', filterable: false,
      Cell: props => <DateDisplay value={ props.value } format = 'l'/>,
    }
  ];

  // * Chayolei columns
  if ( login.modules.chayolei ) { columns.push(
    {
      Header: 'Registered', accessor: 'user_registered',
      Cell: props => <DateDisplay value={ props.value } format = 'l LT'/>,
      filterMethod: ( filter, row ) => {
        if ( filter.value === 'all' ) return true;
        if ( filter.value === 'yes') return !!row[filter.id];
        if ( filter.value === 'no') return !row[filter.id];
      },
      Filter: ({ filter, onChange }) =>
        <select style={{ width: "100%" }} value={filter ? filter.value : "all"}
          onChange={event => onChange(event.target.value)}>
          <option value="all">Show All</option>
          <option value="yes">Registered</option>
          <option value="no">Not Registered</option>
        </select>
    },
    { Header: 'CTH', accessor: 'chayolei',
      Cell: ({ value, original }) => <Toggle checked={ !!value } 
        onChange={ updateToggle( 'chayolei', original.user_id ) } />,
      Filter: yesNoFilterRender(), filterMethod: yesNoFilter
    }
  )};

  // * Chidon columns
  if ( login.modules.chidon ) {
    columns.push(
      { Header: 'Chidon', accessor: 'chidon',
        Cell: ({ value, original }) => <Toggle checked={ !!value } 
          onChange={ updateToggle( 'chidon', original.user_id ) } />,
        Filter: yesNoFilterRender(), filterMethod: yesNoFilter
      }
    );
  }
  
  // * Base Commander columns
  if ( isBC( login.code ) ) {
    columns.push({
      id: 'platoon', Header: 'Platoon', 
      accessor: user => user.platoon ? user.platoon.name : '-'
    });
  }

  // * Institution columns
  if ( isAdmin( login.code ) ) {
    columns.push({
      id: 'base', Header: 'Base', accessor: user => user.school.school_name
    });
  }

  return columns;
}
