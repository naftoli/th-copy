import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Page404 } from 'pages/errors';
import { Spinner, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button, InputGroup, InputGroupAddon } from 'reactstrap';
// rows
import { EditStaffRow } from './rows/EditStaffRow';
// functions
import memoize from 'memoize-one';
import { toast } from 'react-toastify';
import { loginStoreChanged } from 'functions/login';
// state
import { getStaff } from 'store/staff/operations';

class StaffDetailPage extends Component {

  state = {
    updates: {}
  }

  componentDidMount() {
    if ( this.props.staff.length === 0 ) {
      this.loadStaff();
    }
  }

  componentDidUpdate({ login, staff }) {
    if ( loginStoreChanged( login ) )
      this.loadStaff();
    if ( staff !== this.props.staff ) 
      this.setState({ updates: {} });
  }
  // wrapper / alias for prop
  loadStaff = () => { this.props.getStaff(); };
  
  // cache the result for performance
  findStaff = memoize( ( staff, admin_id ) => staff.find( staff => staff.admin_id === admin_id ) );
  getStaff = () => this.findStaff( this.props.staff, parseInt( this.props.match.params.id, 10 ) );
  
  // event handlers
  handleUpdates = ( updates ) => { this.setState({ updates: { ...this.state.updates, ...updates } }) };
  onChange = ({ target }) => { this.handleUpdates({ [target.name]: target.value }) };

  render() {
    const { loading } = this.props;
    let staff = this.getStaff();

    if ( loading && !staff ) return <Spinner size='10' />;
    if ( !staff ) return <Page404 />;

    // get the things we have changed;
    staff = { ...staff, ...this.state.updates };

    // and render the page
    return (
      <div id='StaffDetailPage'>
        
        <p className='title'>Account Information</p>
        <EditStaffRow 
          { ...staff } 
          onChange={ this.onChange }
          />
        {/* <p className='title'>Positions</p>
        <pre>{ JSON.stringify( staff.positions, null, 2 ) }</pre> */}
        <p className='title'>Debug</p>
        <pre>{ JSON.stringify( { state: this.state }, null, 2 ) }</pre>
      </div>
    );
  }
}

const mapStateToProps = ( { staff, login } ) => ({
  ...staff,
  login: login.current_login
})

const mapDispatchToProps = { getStaff }

export default connect( 
  mapStateToProps, mapDispatchToProps 
)( StaffDetailPage );
