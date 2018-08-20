import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Redirect } from 'react-router-dom';
import { Spinner, FontAwesome } from 'components/ui';
import { NavigationTab } from 'components/navigation';
import { PersonalTab, SettingsTab, RankTab } from './tabs';
import { TabContent, TabPane, Nav, Button } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { loginStoreChanged } from 'functions/login';
import { getSoldier, updateSoldier } from 'store/soldiers/operations';
// styles
import './UserPage.scss';

class UserPage extends Component {
  // initial state
  state = {
    soldier: {},  updates: {},
    loading: true,  activeTab: 1
  }
  // load user on page load
  componentDidMount() { 
    this.getSoldier();
  }
  // set the title once we have the info
  componentDidUpdate( prevProps ) {
    // update the page title
    if ( this.state.soldier.user_serial )
      setTitle( `View / Edit ${this.state.soldier.user_serial}` );
    // if the login changed then we should make sure we have the up to date information...
    if ( loginStoreChanged( prevProps.current_login ) && !this.state.loading )
      this.getSoldier();
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
  // handle tabs
  toggle = ( activeTab ) => () => {
    this.setState({ activeTab });
  }
  // handle form changes
  handleUpdate = ( update ) => {
    const updates = filterUpdates( this.state.soldier, { ...this.state.updates, ...update } );
    this.setState({ updates });
  }
  // save changes to the database
  saveChanges = ( event ) => {
    event.preventDefault();
    const { soldier, updates } = this.state;
    // update the soldier
    if ( Object.keys( updates ).length > 0 ) {
      this.props.updateSoldier( soldier.user_id, updates )
      .then( ( response ) => {
        this.setState({ updates: {}, soldier: response.data });
      });
    } else {
      this.setState({ updates: {} })
    }
  }
  // update the soldiers profile page
  updateProfile = ( formData ) => {
    const { soldier } = this.state;
    this.props.updateSoldier( soldier.user_id, formData )
    .then( response => {
      this.setState({ soldier: { ...soldier, ...response.data } });
    });
  }
  // render the page
  render(){
    let { soldier, loading, updates, activeTab } = this.state;
    const updated = Object.keys( updates ).length > 0;
    soldier = { ...soldier, ...updates };
    // if we do not have the soldier...
    if ( soldier === undefined ) {
      return <Redirect to='/bm/users' />;
    }
    // if loading return a spinner
    if ( loading ) {
      return <Spinner size='8' />
    }
    // render the page and it's sub-pages ( tabs )
    return (
      <div id='UserPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />
        <Nav tabs>
          <NavigationTab active={activeTab === 1} onClick={this.toggle(1)}>
            Personal <FontAwesome icon='user'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 2} onClick={this.toggle(2)}>
            Settings <FontAwesome icon='sliders-h'/>
          </NavigationTab>
          <NavigationTab active={activeTab === 3} onClick={this.toggle(3)}>
            Rank <FontAwesome icon='medal'/>
          </NavigationTab>
        </Nav>
        <form onSubmit={ this.saveChanges }>
          <TabContent activeTab={this.state.activeTab}>
            <TabPane tabId={1}>
              <PersonalTab soldier={ soldier } handleChange={ this.handleUpdate } 
                updateProfile={ this.updateProfile } />
            </TabPane>
            <TabPane tabId={2}>
              <SettingsTab soldier={ soldier } handleChange={ this.handleUpdate } 
                getSoldier={this.getSoldier} />
            </TabPane>
            <TabPane tabId={3}>
              <RankTab soldier={ soldier } />
            </TabPane>
          </TabContent>
          { updated && 
            <Button color='primary'>
              <FontAwesome icon='save'/> Save Changes
            </Button>
          }
        </form>
      </div>
    );
  }
}

const mapStateToProps = ( state ) => {
  return {
    current_login: state.login.current_login
  };
}

export default connect( mapStateToProps, { getSoldier, updateSoldier } )( UserPage );