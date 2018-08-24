import React, { Component } from 'react';
import { connect } from 'react-redux';
// import PropTypes from 'prop-types';
// components
import { Prompt } from 'react-router';
import { TabContent, Nav } from 'reactstrap';
import { FontAwesome, LoadingScreen } from 'components/ui';
import { NavigationTab } from 'components/navigation';
// tabs
import { 
  BaseTab, PaymentsTab, ShippingTab, 
  SettingsTab, StaffTab
} from './tabs';
// state
import { getBase, updateBase } from 'store/bases/operations';
import { removeAuth, createAuth } from 'store/staff/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { isAdmin } from 'functions/login';

class BasesPage extends Component {

  static propTypes = {};

  state = {
    base: {}, // the current base
    updates: {}, // the updates we have done
    activeTab: 5, // currently visiable tab
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
  toggle = activeTab => () => this.setState({ activeTab });
  // input changed
  onUpdate = updates => {
    updates = filterUpdates( this.state.base, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  };
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
  updateBase = updates => {
    return this.props.updateBase( this.state.base.school_id, updates )
    .then( base => this.setState({ base }) );
  }
  

  render() {
    let { loading, base, updates, activeTab, valid } = this.state;
    // return a spinner when loading
    if ( loading ) return <LoadingScreen />;
    // update the base info
    base = { ...base, ...updates };
    // is the form updated and valid
    const updated = Object.keys( updates ).length > 0;

    return (
      <div id='BasePage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />
        <Nav tabs>
          <NavigationTab active={activeTab === 1} onClick={this.toggle(1)}>
            { valid.base || <FontAwesome icon='exclamation'/> } Base <FontAwesome icon='school'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 2} onClick={this.toggle(2)}>
            { valid.settings || <FontAwesome icon='exclamation'/> } Settings <FontAwesome icon='sliders-h'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 3} onClick={this.toggle(3)}>
            { valid.shipping || <FontAwesome icon='exclamation'/> } Shipping <FontAwesome icon='shipping-fast'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 4} onClick={this.toggle(4)}>
            Payments <FontAwesome icon='credit-card'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 5} onClick={this.toggle(5)}>
            Staff <FontAwesome icon='users'/>
          </NavigationTab>
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
            isAdmin={ isAdmin( this.props.login.code ) } />

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
