import React, { Component } from 'react';
import {
  Navbar as BoostrapNavbar, NavbarBrand, Nav,
  UncontrolledDropdown, DropdownToggle, DropdownMenu, DropdownItem
} from 'reactstrap';
import './Navbar.scss';
import { LEGACY_URL } from 'components/constants';
import user from 'img/user.svg';
import logo from 'img/logo.svg';

class Navbar extends Component {

  static defaultProps = {
    title: "Tzivos Hashem",
    current_login: {},
    logins: [], 
    onLoginChange: () => {}
  }

  onLoginChange = ( type, id ) => () => {
    this.props.onLoginChange( type, id );
  }

  render(){
    const { title, current_login, logins } = this.props;
    // only render the dropdown if there are options
    const loginItems = logins.map( ( login, index ) => {
      const active = login.type === current_login.type &&  login.id === current_login.id;
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
              <img id="profile-picture" src={ LEGACY_URL + current_login.img || user } alt="profile_picture"/>
              <span>{ current_login.name || `My Accounts` }</span>
            </DropdownToggle>
            <DropdownMenu right>
              <DropdownItem header>Logins</DropdownItem>
              { loginItems }
              <DropdownItem divider />
              <DropdownItem href={`${LEGACY_URL}/admin_profile.php`}>
                <img id="profile-picture" src={ user } alt="profile_picture"/>
                <span>My Account</span>
              </DropdownItem>
            </DropdownMenu>
          </UncontrolledDropdown>
        </Nav>
      </BoostrapNavbar>
    )
  }
}

export default Navbar;