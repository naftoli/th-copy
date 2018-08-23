import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Link } from 'react-router-dom';
import { FontAwesome } from 'components/ui';
import {
  Navbar as BoostrapNavbar, NavbarBrand, Nav,
  UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem
} from 'reactstrap';
import './Navbar.scss';
// functions
import { loginStoreChanged } from 'functions/login';
// icons
import user from 'img/user.svg';
import logo from 'img/logo.svg';

class Navbar extends Component {

  static defaultProps = {
    title: "Tzivos Hashem",
    currentLogin: {},
    logins: [], 
    onLoginChange: () => {}
  }

  onLoginChange = ( type, id ) => () => {
    if ( loginStoreChanged( { type, id } ) )
      this.props.onLoginChange( type, id );
  }

  render(){
    const { title, currentLogin, logins } = this.props;
    // only render the dropdown if there are options
    const loginItems = logins.map( ( login, index ) => {
      const active = login.type === currentLogin.type &&  login.id === currentLogin.id;
      return (
        <DropdownItem key={ index } onClick={ this.onLoginChange( login.type, login.id ) }
          className={ active ? 'active' : ''}>
          <img src={ LEGACY_URL + login.img } alt="profile_picture"/>
          <span>{ login.name }</span>
        </DropdownItem>
      );
    });
    
    return (
      <BoostrapNavbar id="mashpia-navbar">
        <NavbarBrand onClick={ this.props.onClick }>
          <img src={ logo } alt="logo" />
          <span>Menu</span>
        </NavbarBrand>
        <div id="navbar-title" className="mx-auto">{ title }</div>
        <Nav id="navbar-menu" navbar>
          <UncontrolledDropdown>
            <DropdownToggle nav id='nav-login'>
              <img id="profile-picture" src={ LEGACY_URL + currentLogin.img || user } alt="profile_picture"/>
              <span>{ currentLogin.name || `My Accounts` }</span>
            </DropdownToggle>
            <DropdownMenu right>
              <DropdownItem header>Logins</DropdownItem>
              { loginItems }
              <DropdownItem divider />
              <DropdownItem href={`${LEGACY_URL}/admin_profile.php`}>
                <img id="profile-picture" src={ user } alt="profile_picture"/>
                <span>My Account</span>
              </DropdownItem>
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

export default Navbar;
