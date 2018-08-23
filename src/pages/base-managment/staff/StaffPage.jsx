import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { Table, Callout, InlineSync, FontAwesome } from 'components/ui';
import { Button, ButtonGroup } from 'reactstrap';
// modals
import NewStaffModal from './NewStaffModal';
// functions
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { getStaff } from 'store/staff/operations';

class StaffPage extends Component {
  // modal to create staff
  state = { showModal: false }

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Staff' );
    this.getStaff();
  }

  getStaff = () => { this.props.getStaff() }
  toggle = () => this.setState({ showModal: !this.state.showModal });

  toCSV = () => {
    const headers = [];
    const rows = this.props.staff.map( staff => [] );
    arrayToCSV( headers, rows, 'staff' );
  }

  render() {
    const { staff, loading, match } = this.props;

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
    ];

    return (
      <div id='StaffPage'>
        <Callout title='View / Edit Staff Accounts'>
          <p>Staff accounts are any accounts connected to your base.</p>
        </Callout>
        <ButtonGroup>
          <Button onClick={this.toggle} className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Staff Account
          </Button>
          <Button color='primary' onClick={ this.getStaff }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( staff ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Staff (CSV/Excel)
            </Button>
          }
        </ButtonGroup>

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

const mapStateToProps = ( { staff, login } ) => ({
  ...staff,
  login: login.current_login
})

const mapDispatchToProps = { getStaff }

export default connect( mapStateToProps, mapDispatchToProps )( StaffPage );
