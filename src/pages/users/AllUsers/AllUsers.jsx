import React, { Component } from 'react';
import ReactTable from "react-table";
import 'react-table/react-table.css';
import './AllUsers.scss';
import API from 'api/api';

export class AllUsers extends Component {

  constructor( props ){
    super( props );
    this.state = { users: [] }
  }

  componentDidMount(){
    this.getUsers();
  }

  getUsers = () => {
    API.get('/core/users.php').then( response => {
      console.log( response )
      this.setState({ users: response.data });
    });
  }

  render() {
    const { users } = this.state;
  
    const columns = [{
      Header: 'Profile',
      accessor: 'profilePicture',
      Cell: props => <img src={`//mashpia.com/${props.value}`} alt='profile' className='inline-profile'/>,
      className: 'profile-picture', width: 85,
      filterable: false, sortable: false 
    },{ 
      Header: "First Name", 
      accessor: 'first' 
    },{ 
      Header: "Last Name", 
      accessor: 'last' 
    },{
      id: 'platton', // Required because our accessor is not a string
      Header: 'Platton',
      accessor: user => user.platton.class_grade // Custom value accessors!
    }];

    return (
      <div id="all-users">
        <ReactTable data={ users } columns={columns} filterable={true} />
      </div>
    );
  }

}

export default AllUsers;