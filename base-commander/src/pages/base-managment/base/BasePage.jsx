import React, { Component } from 'react';
import { connect } from 'react-redux';
// import PropTypes from 'prop-types';
// components
import { Prompt } from 'react-router';
import { TabContent, Nav } from 'reactstrap';
import { NavigationTabs } from 'components/navigation';
import { LoadingScreen } from 'components/ui';
// tabs
import { 
  BaseTab, PaymentsTab, ShippingTab, 
  SettingsTab, StaffTab
} from './includes/tabs';
// state
import { getBase, updateBase } from 'store/base/bases/operations';
import { removeAuth, createAuth } from 'store/base/staff/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { isAdmin } from 'functions/login';
import { showError } from 'functions/notifications';

class BasesPage extends Component {

  static propTypes = {};

  state = {
    base: {}, // the current base
    updates: {}, // the updates we have done
    activeTab: 1, // currently visiable tab
    loading: true, // loading base or not
    valid: {
      base: true, settings: true, shipping: true
    }
  }

  /********************************* PAGE LIFECYCLE *********************************/
  // update the base on mount
  componentDidMount() {
    this.loadBase()
  }
  // load the base from the API
  loadBase = () => {
    const { login, history, match } = this.props;
    let school_id = parseInt( match.params.id, 10 );
    // if we are on the wrong base, fix the URL
    if ( !isAdmin( login.code ) && school_id !== login.id ) {
      this.setState({ loading: true, updates: {} }, () => { // clear any updates before navigating away...
        history.replace( match.path.replace(':id([0-9]+)', login.id) );
      });
      school_id = login.id; // and load the correct school
    } else this.setState({ loading: true, updates: {} });
    // load the final base
    this.props.getBase( school_id )
      .then( base => {
        setTitle( `Base #${base.school_number}` );
        this.setState({ base, loading: false })
      })
      .catch( error => toast.error( error.message ) );
  }

  /********************************* EVENT HANDLERS *********************************/
  toggle = activeTab => {
    this.setState({ activeTab })
  };
  // input changed
  onUpdate = updates => {
    updates = filterUpdates( this.state.base, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  };
  // update the validity of the forms
  updateValid = tab => status => {
    if ( this.state.valid[tab] !== status ) {
      this.setState({ valid: { ...this.state.valid, [tab]: status } })
    }
  }
  // save the changes to the base
  saveChanges = event => {
    event && event.preventDefault();
    // validate form
    const isInvalid = Object.values( this.state.valid ).includes( false );
    if ( isInvalid ) return toast.error( 'Please correct all invalid feilds' );
    // save the base
    this.updateBase( this.state.updates )
    .then( () => this.setState({ updates: {} }) );
  }
  // update the base
  updateBase = updates => {
    return showError(
      this.props.updateBase( this.state.base.school_id, updates )
      .then( base => this.setState({ base }) )
    );
  }
  

  render() {
    const { login } = this.props;
    let { loading, base, updates, activeTab, valid } = this.state;
    // return a spinner when loading
    if ( loading ) return <LoadingScreen />;
    // update the base info
    base = { ...base, ...updates };
    // is the form updated and valid
    const updated = Object.keys( updates ).length > 0;

    const navProps = { activeTab, onClick: this.toggle };
    const tabs = [
      { tab: 1, ...navProps, icon: 'school',    title: 'Base', valid: valid.base },
      { tab: 2, ...navProps, icon: 'sliders-h', title: 'Settings', valid: valid.settings },
      { tab: 3, ...navProps, icon: 'truck',     title: 'Shipping', valid: valid.shipping },
      { tab: 4, ...navProps, icon: 'credit-card', title: 'Credit Cards' },
      { tab: 5, ...navProps, icon: 'users',     title: 'Commanders' }
    ]

    return (
      <div id='BasePage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />
        
        <Nav tabs>
          <NavigationTabs tabs={ tabs } />
        </Nav>

        <TabContent activeTab={ activeTab }>

          <BaseTab 
            tabId={ 1 }
            base={ base }
            updated={ updated }
            onUpdate={ this.onUpdate } 
            onSubmit={ this.saveChanges }
            updateBase={ this.updateBase }
            onValidChange={ this.updateValid('base') } />

          <SettingsTab 
            tabId={ 2 } 
            base={ base }
            login={ login }
            updated={ updated }
            refresh={ this.loadBase }
            onUpdate={ this.onUpdate } 
            onSubmit={ this.saveChanges }
            createAuth={ this.props.createAuth }
            removeAuth={ this.props.removeAuth }
            onValidChange={ this.updateValid('settings') } />

          <ShippingTab
            tabId={ 3 }
            base={ base } 
            updated={ updated }
            onUpdate={ this.onUpdate } 
            onSubmit={ this.saveChanges }
            onValidChange={ this.updateValid('shipping') } />

          <PaymentsTab 
            tabId={ 4 }
            schoolId={ base.school_id }
            refresh={ this.loadBase }
            profile={ base.customerProfile }
            isAdmin={ isAdmin( login.code ) } />

          <StaffTab 
            tabId={ 5 } 
            staff={ base.staff }
            updated={ updated }
            refresh={ this.loadBase }
            schoolId={ base.school_id }
            createAuth={ this.props.createAuth }
            removeAuth={ this.props.removeAuth } />

        </TabContent>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
});

const mapDispatchToProps = {
  getBase, updateBase, removeAuth, createAuth
}

export default connect( mapStateToProps, mapDispatchToProps )( BasesPage );
