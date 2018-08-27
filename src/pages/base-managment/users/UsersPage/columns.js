import React from 'react';
import { Link } from 'react-router-dom';
import { ProfilePicture } from 'components/ui';
import { DEFAULT_PROFILE } from 'components/constants';
import { isBC, isAdmin } from 'functions/login';

export default ( code, editPicture ) => {
  // define the table for the page
  let columns = [
    {
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={ props.value } className='inline-profile' 
                        onClick={ editPicture( props.original.user_id ) }/>,
      className: 'profile-picture', width: 85, sortable: false,
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
    },{
      Header: "First Name", accessor: 'first',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Last Name", accessor: 'last',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Serial Number", accessor: 'user_serial',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      id: 'dob',  Header: 'Date Of Birth',
      accessor: user => user.dob, sortable: false
    },{
      id: 'registered',  Header: 'Registered', sortable: false,
      accessor: user => user.user_registered,
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
    { id: 'chayolei',  Header: 'CTH', accessor: user => user.chayoeli ? 'Yes' : 'No' },
    { id: 'chidon',  Header: 'Chidon', accessor: user => user.chidon ? 'Yes' : 'No' }
  ];
  
  if ( isBC( code ) ) {
    columns.push({
      id: 'platoon', Header: 'Platoon', 
      accessor: user => user.platoon ? user.platoon.name : '-'
    });
  }

  if ( isAdmin( code ) ) {
    columns.push({
      id: 'base', Header: 'Base', accessor: user => user.school.school_name
    });
  }

  return columns;
}
