import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import { TabPane } from 'reactstrap';
import { StaffRow, NewStaffRow } from '../rows';
// functions
import { toast } from 'react-toastify';

export class StaffTab extends Component {

  static propTypes = {
    staff: PropTypes.array.isRequired,
    tabId: PropTypes.number.isRequired,
    refresh: PropTypes.func.isRequired,
    schoolId: PropTypes.number.isRequired,
    createAuth: PropTypes.func.isRequired,
    removeAuth: PropTypes.func.isRequired,
  }

  disconnect = ( admin_id ) => {
    const id = this.props.schoolId;
    this.props.removeAuth({ admin_id, id, auth: 'school' })
    .then( this.props.refresh )
    .catch( error => { toast.error( error.message ) } );
  }

  connect = email => {
    const id = this.props.schoolId;
    // create the connection
    this.props.createAuth({ email, id, auth: 'school' })
    .then( this.props.refresh )
    .catch( error => { toast.error( error.message ) } );
  }

  render(){
    const { tabId, staff } = this.props;

    return (
      <TabPane tabId={ tabId }>
        <div id='StaffTab'>
          
          {/* show all the staff and manage them */}
          <NewStaffRow onSubmit={ this.connect }/>

          { staff.map( (staff, index) => 
            <StaffRow key={index} disconnect={ this.disconnect } {...staff} />
          )}

        </div>
      </TabPane>
    )
  }
}
