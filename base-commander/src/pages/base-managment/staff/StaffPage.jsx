import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { ButtonBar, Table, Callout, InlineSync, FontAwesome } from 'components/ui';
import { Button } from 'reactstrap';
// modals
import NewStaffModal from './NewStaffModal';
// functions
import { isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import { getStaff } from 'store/base/staff/operations';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

class StaffPage extends Component {
  // modal to create staff
  state = { showModal: false }

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'Staff' );
    this.getStaff();
  }

  getStaff = () => { this.props.getStaff() }
  toggle = () => this.setState({ showModal: !this.state.showModal });

  toCSV = () => {
    let headers, rows
    if ( isAdmin( this.props.login.code ) ) {
      headers = [
        'Username',   'First Name', 'Last Name', 'E-mail Address',
        'Cell Phone', 'Position',   'Platoon', 'Base'
      ];
      rows = this.props.staff.map( staff => [
        staff.username,   staff.first,  staff.last,
        staff.email,      staff.cell,   staff.position,
        staff.platoon,    staff.school_name
      ]);
    } else {
      headers = [
        'Username', 'First Name', 'Last Name', 'E-mail Address',
        'Cell Phone', 'Position', 'Platoon'
      ];
      rows = this.props.staff.map(staff => [
        staff.username, staff.first, staff.last,
        staff.email, staff.cell, staff.position,
        staff.platoon
      ]);
    }
    //arrayToCSV( headers, rows, 'staff' );
    return dataToCSV( headers, rows );
  }

  render() {
    const { staff, loading, match, login } = this.props;

    let columns = [
      { Header: 'Username', accessor: 'username',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'Password', accessor: 'password',
        Cell: props => <Link to={`${match.path}/${props.original.admin_id}`}>{props.value}</Link> },
      { Header: 'First Name', accessor: 'first' },
      { Header: 'Last Name', accessor: 'last' },
      { Header: 'E-mail Address', accessor: 'email' },
      { Header: 'Cell Phone', accessor: 'cell' },
      { Header: 'Position', accessor: 'position' },
      { Header: 'Platoon', accessor: 'platoon' },
    ];

    if ( isAdmin( login.code ) ) {
      columns.push( { Header: 'Base', accessor: 'school_name' } )
    }

    return (
      <div id='StaffPage' className='full-height'>
        <Callout title='View / Edit Staff Accounts'>
          <p>Staff accounts are any accounts connected to your base.</p>
        </Callout>
        
        <ButtonBar>
          <Button onClick={this.toggle} className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Staff Account
          </Button>
          <Button color='primary' onClick={ this.getStaff }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( staff ) &&
            <CSVLink
              data = { this.toCSV() }
              filename = { "staff.csv" }
              target = "_blank"
            >
              <Button color='primary'>
                <FontAwesome icon='file-download' /> Download Staff (CSV/Excel)              
              </Button>
            </CSVLink>
          }
        </ButtonBar>

        <Table 
          data={ staff } 
          loading={ loading && !staff.length } 
          columns={ columns } 
          pageId='StaffPage' />

        <NewStaffModal 
          isOpen={ this.state.showModal } 
          toggle={ this.toggle } 
          />
      </div>
    )
  }
}

const mapStateToProps = ( { base, login } ) => ({
  ...base.staff,
  login: login.current_login
})

const mapDispatchToProps = { getStaff }

export default connect( mapStateToProps, mapDispatchToProps )( StaffPage );
