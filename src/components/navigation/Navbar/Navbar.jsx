import React, { Component } from 'react';
import {
  Navbar as BoostrapNavbar,
  NavbarBrand
} from 'reactstrap';
import './Navbar.scss';
import { LEGACY_URL } from 'components/constants';
import user from 'img/user.svg';

class Navbar extends Component {
  static defaultProps = {
    profile_picture: user,
    title: "Tzivos Hashem"
  }

  render(){
    return (
      <BoostrapNavbar id="mashpia-navbar">
        <NavbarBrand onClick={ this.props.onClick }>
          <img src="//mashpia.com/mobile/img_new/TH%20Logo-colorful-svg.svg" alt="logo" />
          <span>Menu</span>
        </NavbarBrand>
        <div id="navbar-title" className="mx-auto">
          { this.props.title }
        </div>
        <div id="navbar-menu">
          <a href={`${LEGACY_URL}/admin_profile.php`}>
            <img id="profile-picture" src={ this.props.profile_picture } alt="profile_picture"/>
            <span>My Account</span>
          </a>
        </div>
      </BoostrapNavbar>
    )
  }
}

export default Navbar;