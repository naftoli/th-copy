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
    console.log( logins.length );
    let dropdown = 
      <a>
        <img id="profile-picture" src={ current_login.img || user } alt="profile_picture"/>
        <span>{ current_login.name || `My Accounts` }</span>
      </a>;
    if ( logins.length > 1 ) {
      dropdown =
        <UncontrolledDropdown>
          <DropdownToggle nav caret>
            <img id="profile-picture" src={ current_login.img || user } alt="profile_picture"/>
            <span>{ current_login.name || `My Accounts` }</span>
          </DropdownToggle>
          <DropdownMenu right>
          { logins.map( ( login, index ) => (
            <DropdownItem key={ index } onClick={ this.onLoginChange( login.type, login.id ) }>
              <img src={ LEGACY_URL + login.img } alt="profile_picture"/>
              <span>{ login.name }</span>
            </DropdownItem>
          )) }
          </DropdownMenu>
        </UncontrolledDropdown>;
    }
    
    return (
      <BoostrapNavbar id="mashpia-navbar">
        <NavbarBrand onClick={ this.props.onClick }>
          <img src={ logo } alt="logo" />
          <span>Menu</span>
        </NavbarBrand>
        <div id="navbar-title" className="mx-auto">{ title }</div>
        <Nav id="navbar-menu" navbar>
          { dropdown }
        </Nav>
      </BoostrapNavbar>
    )
  }
}

export default Navbar;