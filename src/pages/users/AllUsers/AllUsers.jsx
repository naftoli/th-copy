import React, { Component } from 'react';
import ReactTable from "react-table";
import ProfilePicture from 'components/ui/ProfilePicture';
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
      this.setState({ users: response.data });
    });
  }

  render() {
    const { users } = this.state;
  
    const columns = [{
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={`//mashpia.com${props.value}`} className='inline-profile'/>,
      className: 'profile-picture', width: 85, filterable: false,
    },{
      Header: "First Name", 
      accessor: 'first' 
    },{
      Header: "Last Name", 
      accessor: 'last' 
    },{
      Header: "Serial Number", 
      accessor: 'user_serial' 
    },{
      id: 'platton', // Required because our accessor is not a string
      Header: 'Platton',
      accessor: user => user.platton.name // Custom value accessors!
    }];

    return (
      <div id="all-users">
        <ReactTable data={ users } columns={columns} filterable={true} className="-striped -highlight" 
          style={{ maxHeight: "80vh" }}/>
      </div>
    );
  }

}

export default AllUsers;