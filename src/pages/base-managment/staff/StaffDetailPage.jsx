import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Page404 } from 'pages/errors';
import { Spinner, FontAwesome } from 'components/ui';
import { Button } from 'reactstrap';
// rows
import EditStaffRow from './rows/EditStaffRow';
import PositionRow from './rows/PositionRow';
import CreatePositionRow from './rows/CreatePositionRow';
// functions
import memoize from 'memoize-one';
import { toast } from 'react-toastify';
import { loginStoreChanged } from 'functions/login';
// state
import { getStaff, updateStaff } from 'store/staff/operations';

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
  save = () => {
    this.props.updateStaff( parseInt( this.props.match.params.id, 10 ), this.state.updates )
    .catch( error => toast.error( error.message ) );
  }

  render() {
    const { loading, login } = this.props;
    const updated = Object.keys( this.state.updates ).length > 0;
    let staff = this.getStaff();

    if ( loading && !staff ) return <Spinner size='10' />;
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

        <div id='save' className={ updated ? 'show' : 'hide' }>
          <Button color='primary' onClick={ this.save }>
            <FontAwesome icon='save' /> Save Changes
          </Button>
        </div>

        <p className='title'>Positions</p>

        <CreatePositionRow 
          adminId={ staff.admin_id } />

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
  getStaff, updateStaff 
}

export default connect( 
  mapStateToProps, mapDispatchToProps 
)( StaffDetailPage );
