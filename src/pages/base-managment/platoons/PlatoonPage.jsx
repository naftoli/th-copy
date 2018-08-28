import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { SaveButton } from 'components/buttons';
import { Link, Redirect } from 'react-router-dom';
import { StaffRow, NewStaffRow, PlatoonRow } from './rows';
import { LoadingScreen, Callout } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { getPlatoon, updatePlatoon } from 'store/platoons/operations';
import { removeAuth, createAuth } from 'store/staff/operations';
import { filterUpdates } from 'functions/events';

export class PlatoonPage extends Component {
  // initial state
  state = {
    platoon: {}, updates: {}, loading: true
  }

  // non-destructivly update the state
  handleUpdate = ( update ) => {
    const updates = filterUpdates( this.state.platoon, { ...this.state.updates, ...update } );
    this.setState({ updates });
  }
  // handle selects
  handleInputChange = ({ target }) => { this.handleUpdate({ [target.name]: target.value }) };
  handleSelectChange = ( option ) => { this.handleUpdate({ [option.id]: option.value }) };

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'Platoon' );
    this.getPlatoon();
  }

  getPlatoon = () => {
    const { match, getPlatoon } = this.props;
    this.setState({ loading: true });
    getPlatoon( match.params.id )
    .then( platoon => { this.setState({ platoon, loading: false }); })
    .catch( error => {
      toast.error( error.message );
      this.setState({ platoon: undefined }); }
    );
  }

  save = ( event ) => {
    event && event.preventDefault();
    const { updates, platoon } = this.state;
    this.props.updatePlatoon( platoon.class_id, updates )
    .then( platoon => this.setState({ platoon, updates: {} }) );
  }

  disconnect = ( admin_id ) => {
    const { class_id: id } = this.state.platoon;
    this.props.removeAuth({ admin_id, id, auth: 'class' })
    .then( this.getPlatoon )
    .catch( error => { toast.error( error.message ) } );
  }

  connect = ( email ) => {
    const { class_id: id } = this.state.platoon;
    // create the connection
    this.props.createAuth( { email, id, auth: 'class' } )
    .then( this.getPlatoon )
    .catch( error => { toast.error( error.message ) } );
  }

  render() {
    let { platoon, loading, updates } = this.state;

    if ( platoon === undefined ) return <Redirect to='/platoons' />;
    if ( loading ) return <LoadingScreen  Callout />
    
    const { staff } = platoon;

    const inputProps = { onChange: this.handleInputChange };
    const selectProps = { onChange: this.handleSelectChange };

    const updated = Object.keys( this.state.updates ).length > 0;
    platoon = { ...platoon, ...updates };

    return (
      <div id='PlatoonPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />

        <p className='title'>Platoon Information</p>
        <Callout icon={ false }>
          <p>Please note that we will use this information when sending updates and printing Mission Sheets. Please make sure it is up to date.</p>
          <p>To edit staff (Teacher) accounts please look at the <Link to='/bm/staff'>Staff tab under Base Managment.</Link></p>
        </Callout>

        <form onSubmit={ this.save }>
          <PlatoonRow platoon={ platoon } 
            inputProps={ inputProps } selectProps={ selectProps } />

          <SaveButton show={ updated } />
        </form>
        
        {/* show all the staff and manage them */}
        <p className='title'>Connected Staff Accounts</p>

        <NewStaffRow onSubmit={ this.connect } />

        { staff.map( (staff, index) => 
          <StaffRow key={index} disconnect={this.disconnect} {...staff} />
        )}

      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

const mapDispatchToProps = { 
  getPlatoon, updatePlatoon, 
  removeAuth, createAuth
}

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonPage );
