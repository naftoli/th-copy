import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
import SidebarDropdown from './SidebarDropdown';
import { NavLink } from 'react-router-dom'

class SidebarItem extends Component {

  render(){
    // This is a dropdown
    if ( this.props.children ) {
      return <SidebarDropdown { ...this.props } />;
    }

    // Generate link to React-Router page
    let link = <NavLink exact to={ this.props.path }> { this.props.icon } { this.props.label } </NavLink>;

    // use a standard A tag if page is outside of this system
    if ( this.props.legacy ) {
      link = <a href={LEGACY_URL + this.props.path}> { this.props.icon } { this.props.label } </a>;
    }

    // return the link wrapped in an LI
    return <li> { link } </li>;
  }
}

SidebarItem.defaultProps = {
  label: '',
  icon: false,
  children: false,
  legacy: false,
  path: '#'
}

export default SidebarItem;