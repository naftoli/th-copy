import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';

import { NavLink } from 'react-router-dom'
import SidebarDropdown from './SidebarDropdown';
import { FontAwesome } from 'components/ui/Icons';

class SidebarItem extends Component {

  static defaultProps = {
    label: '',
    icon: false,
    items: false,
    legacy: false,
    path: '#'
  }

  render(){
    let { items, icon, legacy, label, path } = this.props;
    
    // This is a dropdown
    if ( items ) {
      return <SidebarDropdown { ...this.props } />;
    }

    if ( icon && !legacy ) {
      icon = <FontAwesome icon={ icon } />
    } else if ( icon ) {
      icon = <img src={ LEGACY_URL + icon } alt={ label } />
    }

    // Generate link to React-Router page
    let link = (
      <NavLink exact to={ path }>
        { icon }{ label }
      </NavLink>
    );

    // use a standard A tag if page is outside of this system
    if ( legacy ) {
      link = (
        <a href={ LEGACY_URL + path }>
          { icon }{ label }
        </a>
      );
    }

    // return the link wrapped in an LI
    return <li> { link } </li>;
  }
}

export default SidebarItem;