import React, { Component } from 'react';
// components
import { TabContent, TabPane, Nav, NavItem, NavLink } from 'reactstrap';
import { Redirect } from 'react-router-dom';
import Spinner from 'components/ui/Spinner';
// functions
import { getSoldier } from 'store/soldiers/operations';
import { connect } from 'react-redux';
import { setTitle } from 'functions/utils';
// styles
import './UserPage.scss';

class UserPage extends Component {

  state = {
    soldier: false,
    loading: true,
    activeTab: 1
  }

  componentDidMount() {
    const { soldiers, match, getSoldier } = this.props;
    // get all the info for the current soldier
    getSoldier( match.params.id ).then( soldier => {
      this.setState({ soldier: soldier, loading: false });
    }); 
    // see if we have the user...
    const user_id = parseInt( match.params.id, 10 ); // convert the type
    const current_soldier = soldiers.find( soldier => soldier.user_id === user_id );
    if ( current_soldier ) this.setState({ soldier: current_soldier, loading: false });
  }

  componentDidUpdate() {
    const { soldier } = this.state;
    // update the page title
    if ( soldier ) {
      setTitle( `View / Edit ${soldier.user_serial}` );
    }
  }

  toggle = ( tab ) => () => {
    this.setState({ activeTab: tab });
  }

  isActive = ( tab ) => {
    return this.state.activeTab === tab ? 'active': '';
  }

  render(){
    const { soldier, loading } = this.state;
    // if we do not have the soldier...
    if ( soldier === undefined ) {
      return <Redirect to='/users' />;
    }
    // if loading return a spinner
    if ( loading ) {
      return <Spinner size='5' />
    }
    // render the page and it's sub-pages ( tabs )
    return (
      <div id='UserPage'>
        <Nav tabs>
          <NavItem>
            <NavLink className={this.isActive(1)} onClick={this.toggle(1)}>
              Personal
            </NavLink>
          </NavItem>
          <NavItem>
            <NavLink className={this.isActive(2)} onClick={this.toggle(2)}>
              Address
            </NavLink>
          </NavItem>
          <NavItem>
            <NavLink className={this.isActive(3)} onClick={this.toggle(3)}>
              Platoon
            </NavLink>
          </NavItem>
          <NavItem>
            <NavLink className={this.isActive(4)} onClick={this.toggle(4)}>
              Settings
            </NavLink>
          </NavItem>
          <NavItem>
            <NavLink className={this.isActive(5)} onClick={this.toggle(5)}>
              Registration
            </NavLink>
          </NavItem>
          <NavItem>
            <NavLink className={this.isActive(6)} onClick={this.toggle(6)}>
              Rank
            </NavLink>
          </NavItem>
        </Nav>
        <TabContent activeTab={this.state.activeTab}>
          <TabPane tabId={1}>
            <h1>Personal</h1>
          </TabPane>
          <TabPane tabId={2}>
            <h1>Address</h1>
          </TabPane>
          <TabPane tabId={3}>
            <h1>Platoon</h1>
          </TabPane>
          <TabPane tabId={4}>
            <h1>Settings</h1>
            <pre>{ JSON.stringify( soldier, null, 2 ) }</pre>
          </TabPane>
          <TabPane tabId={5}>
            <h1>Registration</h1>
          </TabPane>
          <TabPane tabId={6}>
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