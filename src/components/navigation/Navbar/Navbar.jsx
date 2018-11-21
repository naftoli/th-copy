import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
// components
import { Link } from 'react-router-dom';
import { FontAwesome, BaseLogo } from 'components/ui';
import {
  Navbar as BoostrapNavbar, NavbarBrand, Nav,
  UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem
} from 'reactstrap';
import './Navbar.scss';
// functions
import { loginStoreChanged } from 'functions/login';
// icons
import user from 'img/user.svg';
import logo from 'img/logos/th.svg';

class Navbar extends Component {

  static defaultProps = {
    title: "Tzivos Hashem",
    currentLogin: {},
    logins: [], 
    showMenu: true,
    onLoginChange: () => {}
  }

  onLoginChange = ( login ) => () => {
    const { type, id } = login;
    // check if it is blank, if so take them to the homepage
    if ( this.props.location.pathname === '/myaccount' ) {
      this.props.history.push('/');
    }
    // and change it if it changed
    if ( loginStoreChanged( { type, id } ) ) {
      this.props.onLoginChange( type, id );
    }
  }

  isActive( login ) {
    const { currentLogin, location } = this.props;
    // nothing is selected on the myaccount page
    if ( location.pathname === '/myaccount' ) {
      return false
    // valid accounts are highlighted normally
    } else {
      return login.type === currentLogin.type && login.id === currentLogin.id;
    }
  }

  render(){
    const { title, currentLogin, logins, showMenu } = this.props;
    // only render the dropdown if there are options
    const loginItems = logins.map( ( login, index ) => {
      const active = this.isActive( login );
      return (
        <DropdownItem key={ index } className={ active ? 'active' : ''}
            onClick={ this.onLoginChange( login ) } >
          <BaseLogo src={ login.img } alt="logo" />
          <span>{ login.name }</span>
        </DropdownItem>
      );
    });
    
    return (
      <BoostrapNavbar id="mashpia-navbar">

        <NavbarBrand onClick={ this.props.onClick }>
          <img src={ logo } alt="logo" />
          <span>{ showMenu ? 'Menu' : 'TH'}</span>
        </NavbarBrand>

        <div id="navbar-title" className="mx-auto">{ title }</div>

        <Nav id="navbar-menu" navbar>
          <UncontrolledDropdown>

            <DropdownToggle nav id='nav-login'>
              <BaseLogo src={ currentLogin.img || user } alt="logo" />
              <span>{ currentLogin.name || `My Accounts` }</span>
            </DropdownToggle>

            <DropdownMenu right>
              <DropdownItem header>Logins</DropdownItem>
              { loginItems }
              <DropdownItem header>Links</DropdownItem>

              <Link to={'/myaccount'}>
                <DropdownItem>
                  <img id="profile-picture" src={ user } alt="profile_picture"/>
                  <span>My Account</span>
                </DropdownItem>
              </Link>

              <Link to={'/logout'}>
                <DropdownItem>
                  <FontAwesome icon="sign-out-alt" />
                  <span>Logout</span>
                </DropdownItem>
              </Link>
            </DropdownMenu>

          </UncontrolledDropdown>
        </Nav>
      </BoostrapNavbar>
    )
  }
}

export default withRouter( Navbar );
