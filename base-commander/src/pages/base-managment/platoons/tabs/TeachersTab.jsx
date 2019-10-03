import React, { Component } from 'react';
// components
import { TabPane } from 'reactstrap';
import { NewStaffRow, StaffRow } from 'components/rows';
// functions
import { toast } from 'react-toastify';

export class TeachersTab extends Component {

  //  disconnect and connect staff
  disconnect = ( admin_id ) => {
    const { class_id: id } = this.props.platoon;
    this.props.removeAuth({ admin_id, id, auth: 'class' })
    .then( this.props.refresh )
    .catch( error => { toast.error( error.message ) } );
  }

  // create the connection
  connect = ({ email, username }) => {
    const { class_id: id } = this.props.platoon;
    this.props.createAuth( { email, username, id, auth: 'class' } )
    .then( this.props.refresh )
    .catch( error => { toast.error( error.message ) } );
  }

  render(){
    const { platoon, tabId } = this.props;

    return (
      <TabPane tabId={ tabId } id='TeachersTab' >

        <NewStaffRow onSubmit={ this.connect } />

        <div id='teachers'>
          { platoon.staff.map( ( staff, index ) => 
            <StaffRow key={index} disconnect={this.disconnect} {...staff} />
          )}
        </div>

      </TabPane>
    );
  }
}
