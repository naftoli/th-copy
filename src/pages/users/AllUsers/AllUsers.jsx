import React, { Component } from 'react';
import { connect } from 'react-redux';
import ReactTable from "react-table";
import { Link } from 'react-router-dom';
import ProfilePicture from 'components/ui/ProfilePicture';
import 'react-table/react-table.css';
import './AllUsers.scss';
import { getSoldiers } from 'store/soldiers/operations';

export class AllUsers extends Component {

  componentDidMount(){
    this.props.getSoldiers();
  }

  render() {
    const { soldiers } = this.props;
  
    const columns = [{
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={`//mashpia.com${props.value}`} className='inline-profile'/>,
      className: 'profile-picture', width: 85, filterable: false,
    },{
      Header: "First Name", 
      accessor: 'first',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Last Name", 
      accessor: 'last',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Serial Number", 
      accessor: 'user_serial',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      id: 'base', // Required because our accessor is not a string
      Header: 'Base',
      accessor: user => user.school.school_name
    },{
      id: 'platton', // Required because our accessor is not a string
      Header: 'Platton',
      accessor: user => user.platton.name
    }];

    return (
      <div id="all-users">
        <ReactTable data={ soldiers } columns={columns} filterable={true} className="-striped -highlight" 
          style={{ maxHeight: "90vh" }}/>
      </div>
    );
  }

}

const mapStateToProps = ( state ) => {
  return {
    soldiers: state.soldiers.soldiers
  };
}

export default connect( mapStateToProps, { getSoldiers } )( AllUsers );