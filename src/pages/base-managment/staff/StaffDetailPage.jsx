import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Page404 } from 'pages/errors';
import { LoadingScreen } from 'components/ui';
import { SaveButton } from 'components/buttons';
// rows
import EditStaffRow from './rows/EditStaffRow';
import PositionRow from './rows/PositionRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
import memoize from 'memoize-one';
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
// state
import { getStaff, updateStaff, createAuth } from 'store/staff/operations';

class StaffDetailPage extends Component {

  state = {
    updates: {}
  }

  componentDidMount() {
    setTitle( 'Staff Account' )
    if ( this.props.staff.length === 0 ) {
      this.loadStaff();
    }
  }

  componentDidUpdate({ staff }) {
    if ( staff !== this.props.staff ) 
      this.setState({ updates: {} });
  }
  // wrapper / alias for prop
  loadStaff = () => { this.props.getStaff(); };
  
  // cache the result for performance
  findStaff = memoize( ( staff, admin_id ) => staff.find( staff => staff.admin_id === admin_id ) );
  getStaff = () => this.findStaff( this.props.staff, parseInt( this.props.match.params.id, 10 ) );
  
  // event handlers
  handleUpdates = updates => {
    updates = filterUpdates( this.getStaff(), { ...this.state.updates, ...updates } );
    this.setState({ updates });
  };
  onChange = ({ target }) => { this.handleUpdates({ [target.name]: target.value }) };
  save = () => {
    this.props.updateStaff( parseInt( this.props.match.params.id, 10 ), this.state.updates )
    .catch( error => toast.error( error.message ) );
  }
  // show notification for errors
  createAuth = ( auth ) => {
    this.props.createAuth( auth )
    .then( () => toast.info('Position Created') )
    .catch( error => toast.error( error.message ));
  }

  render() {
    const { loading, login } = this.props;
    const updated = Object.keys( this.state.updates ).length > 0;
    let staff = this.getStaff();

    if ( loading && !staff ) return <LoadingScreen />;
    if ( !staff ) return <Page404 />;

    // get the things we have changed;
    staff = { ...staff, ...this.state.updates };

    // and render the page
    return (
      <div id='StaffDetailPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />

        <p className='title'>Account Information</p>

        <EditStaffRow 
          { ...staff } 
          onChange={ this.onChange }
          />

        <SaveButton show={ updated } onClick={ this.save }/>

        <p className='title'>Positions</p>

        <CreatePositionRow 
          adminId={ staff.admin_id }
          showCreateButton
          onCreate={ this.createAuth }
          isAdmin={ isAdmin( login.code ) }
          />

        { staff.positions.map( 
          ( position, index ) => <PositionRow key={ index } { ...position } />) 
        }
      </div>
    );
  }
}

const mapStateToProps = ( { staff, login } ) => ({
  ...staff,
  login: login.current_login
})

const mapDispatchToProps = { 
  getStaff, updateStaff, createAuth
}

export default connect( 
  mapStateToProps, mapDispatchToProps 
)( StaffDetailPage );
