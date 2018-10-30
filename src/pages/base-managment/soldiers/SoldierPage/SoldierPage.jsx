import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Redirect } from 'react-router-dom';
import { TabContent, Nav } from 'reactstrap';
import { LoadingScreen, FontAwesome } from 'components/ui';
import { NavigationTab } from 'components/navigation';
import {
  PersonalTab,  RankTab,
  SettingsTab,  RegistrationTab,
} from './tabs';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { removeAuth, createAuth } from 'store/base/staff/operations';
import { createNotifcation, updateNotifcation } from 'functions/notifications';
import { getSoldier, updateSoldier, deleteSoldier } from 'store/base/soldiers/operations';
// styles
import './SoldierPage.scss';

class SoldierPage extends Component {
  // initial state
  state = {
    soldier: {},  updates: {},
    loading: true,  activeTab: 1,
    valid: {
      soldier: true, settings: true
    }
  }
  // load user on page load
  componentDidMount() { 
    this.getSoldier();
  }
  // set the title once we have the info
  componentDidUpdate() {
    // update the page title
    if ( this.state.soldier && this.state.soldier.user_serial )
      setTitle( `Soldier #${this.state.soldier.user_serial}` );
  }
  // get the soldier for the page
  getSoldier = () => {
    const { match, getSoldier } = this.props;
    this.setState({ loading: true });
    getSoldier( match.params.id )
    .then( soldier => { this.setState({ soldier, loading: false }); })
    .catch( error => {
      toast.error( error.message );
      this.setState({ soldier: undefined }); }
    );
  }
  // delete soldier
  deleteSoldier = user_id =>
    this.props.deleteSoldier( user_id )
    .then( this.getSoldier );
  
  createAuth = auth =>
    this.props.createAuth( auth )
    .then( this.getSoldier );
  
  removeAuth = auth =>
    this.props.removeAuth( auth )
    .then( this.getSoldier );

  // handle tabs
  toggleTab = activeTab =>
    this.setState({ activeTab });
  // handle form changes
  onUpdate = ( update ) => {
    const updates = filterUpdates( this.state.soldier, { ...this.state.updates, ...update } );
    this.setState({ updates });
  }
  // update if the tab is valid or not
  updateValid = tab => status => {
    if ( this.state.valid[tab] !== status ) {
      this.setState({ valid: { ...this.state.valid, [tab]: status } })
    }
  }
  // save changes to the database
  saveChanges = ( event ) => {
    event && event.preventDefault();
    const { soldier, updates, valid } = this.state;
    // validate form
    const isInvalid = Object.values( valid ).includes( false );
    if ( isInvalid ) return toast.error( 'Please correct all invalid feilds' );
    // update the soldier
    const toast_id = createNotifcation('Updating Soldier');
    this.props.updateSoldier( soldier.user_id, updates )
    .then( soldier => {
      updateNotifcation( toast_id, 'Soldier Updated!', '', true )
      this.setState({ updates: {}, soldier })
    })
    .catch( error => updateNotifcation( toast_id, '', error.message, false ) );
  }
  // update the soldiers profile page
  updateProfile = ( formData ) => {
    const { soldier } = this.state;
    this.props.updateSoldier( soldier.user_id, formData )
    .then( ({ data }) => this.setState({ updates: {}, soldier: data }) );
  }
  // render the page
  render(){
    const { login } = this.props;
    let { soldier, loading, updates, activeTab, valid } = this.state;

    // if we do not have the soldier...
    if ( soldier === undefined )
      return <Redirect to='/bm/soldiers' />;
    // if loading return a LoadingScreen
    if ( loading ) return <LoadingScreen size='8' />;

    soldier = { ...soldier, ...updates };
    const updated = Object.keys( updates ).length > 0;

    const navProps = { onClick: this.toggleTab, activeTab };

    // render the page and it's sub-pages ( tabs )
    return (
      <div id='SoldierPage'>
        <Prompt 
          when={ updated } 
          message="You have unsaved changes. Are you sure you want to leave?" />
        <Nav tabs>
          
          <NavigationTab tab={1} icon='user' { ...navProps }>
           { valid.soldier || <FontAwesome icon='exclamation'/> } Soldier
          </NavigationTab>
          
          <NavigationTab tab={2} icon='sliders-h' { ...navProps }>
           { valid.settings || <FontAwesome icon='exclamation'/> } Settings
          </NavigationTab>
          
          <NavigationTab tab={3} icon='medal' { ...navProps }>
            Rank
          </NavigationTab>

          <NavigationTab tab={4} icon='registered' { ...navProps }>
            Registration
          </NavigationTab>

        </Nav>
        <TabContent activeTab={this.state.activeTab}>
          
          <PersonalTab
            tabId={ 1 }
            login={ login }
            soldier={ soldier }
            updated={ updated } 
            onSubmit={ this.saveChanges }
            handleChange={ this.onUpdate } 
            updateProfile={ this.updateProfile }
            onValidChange={ this.updateValid('soldier') }/>

          
          <SettingsTab 
            tabId={ 2 }
            login={ login }
            soldier={ soldier }
            updated={ updated } 
            onSubmit={ this.saveChanges }
            removeAuth={ this.removeAuth }
            createAuth={ this.createAuth }
            handleChange={ this.onUpdate }
            deleteSoldier={ this.deleteSoldier }
            onValidChange={ this.updateValid('settings') } />
          
          <RankTab 
            tabId={ 3 } 
            soldier={ soldier } />

          <RegistrationTab 
            tabId={ 4 } 
            soldier={ soldier } />

        </TabContent>
      </div>
    );
  }
}

const mapStateToProps = ( state ) => {
  return {
    login: state.login.current_login
  };
}

const mapDispatchToProps = {
  removeAuth, createAuth,
  getSoldier, updateSoldier, deleteSoldier
}

export default connect( mapStateToProps, mapDispatchToProps )( SoldierPage );
