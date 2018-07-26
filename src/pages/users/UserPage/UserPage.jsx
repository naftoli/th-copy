import React, { Component } from 'react';
// components
import { TabContent, TabPane, Nav, NavItem, NavLink, Button } from 'reactstrap';
import { Redirect } from 'react-router-dom';
import { Prompt } from 'react-router';
import Spinner from 'components/ui/Spinner';
import PersonalTab from './PersonalTab';
import SettingsTab from './SettingsTab';
import RankTab from './RankTab';
// functions
import { getSoldier, updateSoldier } from 'store/soldiers/operations';
import { connect } from 'react-redux';
import { setTitle } from 'functions/utils';
import { loginChanged } from 'functions/login';
// styles
import './UserPage.scss';

// navigation tab details
const NavigationTab = ( props ) => (
  <NavItem>
    <NavLink { ...props } >
      { props.children }
    </NavLink>
  </NavItem>
);

class UserPage extends Component {
  // initial state
  state = {
    soldier: {},  updates: {},
    loading: true,  activeTab: 3
  }
  // load user on page load
  componentDidMount() { 
    this.getSoldier();
  }
  // set the title once we have the info
  componentDidUpdate( prevProps ) {
    if ( this.state.soldier ) {
      setTitle( `View / Edit ${this.state.soldier.user_serial}` );
    }
    // if the login changed then we should make sure we have the up to date information...
    if ( loginChanged( this.props.current_login, prevProps.current_login ) && !this.state.loading )
      this.getSoldier();
  }

  // get the soldier for the page
  getSoldier = () => {
    const { match, getSoldier } = this.props;
    getSoldier( match.params.id )
      .then( soldier => {
        this.setState({ soldier, loading: false });
      }).catch( error => {
        this.setState({ soldier: undefined });
      });
  }
  
  // handle tabs
  toggle = ( tab ) => () => {
    this.setState({ activeTab: tab });
  }
  // handle tab styles
  isActive = ( tab ) => {
    return this.state.activeTab === tab ? 'active': '';
  }
  // handle form changes
  handleUpdate = ( update ) => {
    // non-destructivly update the state
    const soldier = Object.assign( {}, this.state.soldier, update );
    const updates = Object.assign( {}, this.state.updates, update );
    this.setState({ soldier, updates });
  }
  // save changes to the database
  saveChanges = ( event ) => {
    event.preventDefault();
    const { soldier, updates } = this.state;
    // update the soldier
    this.props.updateSoldier( soldier.user_id, updates )
    .then( ( response ) => {
      this.setState({ updates: {}, soldier: response.data });
    });
  }
  // render the page
  render(){
    const { soldier, loading, updates } = this.state;
    const updated = Object.keys( updates ).length > 0;
    // if we do not have the soldier...
    if ( soldier === undefined ) {
      return <Redirect to='/users' />;
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
          <NavigationTab className={this.isActive(1)} onClick={this.toggle(1)}>
            Personal
          </NavigationTab>
          <NavigationTab className={this.isActive(2)} onClick={this.toggle(2)}>
            Settings + Platoon
          </NavigationTab>
          <NavigationTab className={this.isActive(3)} onClick={this.toggle(3)}>
            Rank
          </NavigationTab>
          <NavigationTab className={this.isActive(4)} onClick={this.toggle(4)}>
            Debug
          </NavigationTab>
        </Nav>
        <form onSubmit={ this.saveChanges }>
          <TabContent activeTab={this.state.activeTab}>
            <TabPane tabId={1}>
              <PersonalTab soldier={ soldier } handleChange={ this.handleUpdate } />
            </TabPane>
            <TabPane tabId={2}>
              <SettingsTab soldier={ soldier } handleChange={ this.handleUpdate } />
            </TabPane>
            <TabPane tabId={3}>
              <RankTab soldier={ soldier } />
            </TabPane>
            <TabPane tabId={4}>
              <pre>{ JSON.stringify( soldier, null, 2 ) }</pre>
            </TabPane>
          </TabContent>
          { updated &&
            <Button color='primary'>Save Changes</Button>
          }
        </form>
      </div>
    );
  }
}

const mapStateToProps = ( state ) => {
  return {
    ...state.soldiers,
    current_login: state.login.current_login
  };
}

export default connect( mapStateToProps, { getSoldier, updateSoldier } )( UserPage );