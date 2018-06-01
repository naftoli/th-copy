import React, { Component } from 'react';
import {
  Navbar as BoostrapNavbar,
  NavbarBrand
} from 'reactstrap';
import './Navbar.scss';

class Navbar extends Component {
  render(){
    return (
      <BoostrapNavbar id="mashpia-navbar">
        <NavbarBrand onClick={ this.props.onClick }>
          <img src="//mashpia.com/mobile/img_new/TH%20Logo-colorful-svg.svg" alt="logo" />
          <span>Menu</span>
        </NavbarBrand>
      </BoostrapNavbar>
    )
  }
}

export default Navbar;