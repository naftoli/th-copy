import React, { Component } from 'react';
import ReactTable from "react-table";
import 'react-table/react-table.css';
import API from 'api/api';

export class AllUsers extends Component {

  constructor( props ){
    super( props );
    this.state = { loading: true, users: [] }
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
    const data = [{
      name: 'Billy Linsley',
      age: 15,
      friend: {
        name: 'Jason Maurer',
        age: 15,
      }
    },{
      name: 'Tanner Linsley',
      age: 26,
      friend: {
        name: 'Bob Maurer',
        age: 23,
      }
    },{
      name: 'Zan Linsley',
      age: 26,
      friend: {
        name: 'Chaim Maurer',
        age: 90,
      }
    },{
      name: 'Billy Linsley',
      age: 27,
      friend: {
        name: 'Sally Maurer',
        age: 65,
      }
    }];
  
    const columns = [{
      Header: 'Name',
      accessor: 'name' // String-based value accessors!
    }, {
      Header: 'Age',
      accessor: 'age',
      Cell: props => <span className='number'>{props.value}</span> // Custom cell components!
    }, {
      id: 'friendName', // Required because our accessor is not a string
      Header: 'Friend Name',
      accessor: d => d.friend.name // Custom value accessors!
    }, {
      Header: props => <span>Friend Age</span>, // Custom header components!
      accessor: 'friend.age'
    }];

    return <ReactTable data={data} columns={columns} filterable={true} />
  }

}

export default AllUsers;