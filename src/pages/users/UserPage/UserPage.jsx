import React, { Component } from 'react';
// components
import { TabContent, TabPane, Nav, NavItem, NavLink } from 'reactstrap';
import { Redirect } from 'react-router-dom';
import Spinner from 'components/ui/Spinner';
import PersonalTab from './PersonalTab';
// functions
import { getSoldier } from 'store/soldiers/operations';
import { connect } from 'react-redux';
import { setTitle } from 'functions/utils';
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
    loading: true,  activeTab: 1
  }
  // load user on page load
  componentDidMount() {
    const { soldiers, match, getSoldier } = this.props;
    getSoldier( match.params.id ).then( soldier => {
      this.setState({ soldier: soldier, loading: false });
    });
  }
  // set the title once we have the info
  componentDidUpdate() {
    if ( this.state.soldier ) {
      setTitle( `View / Edit ${this.state.soldier.user_serial}` );
    }
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
    const tmp_soldier = Object.assign( {}, this.state.soldier, update );
    const updates = Object.assign( {}, this.state.updates, update );
    this.setState({
      soldier: tmp_soldier,
      updates: updates
    });
  }
  // render the page
  render(){
    const { soldier, loading } = this.state;
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
        </Nav>
        <TabContent activeTab={this.state.activeTab}>
          <TabPane tabId={1}>
            <PersonalTab soldier={ soldier } handleChange={ this.handleUpdate } />
          </TabPane>
          <TabPane tabId={2}>
            <h1>Settings</h1>
            <pre>{ JSON.stringify( soldier, null, 2 ) }</pre>
          </TabPane>
          <TabPane tabId={3}>
            <h1>Rank</h1>
          </TabPane>
        </TabContent>
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

export default connect( mapStateToProps, { getSoldier } )( UserPage );