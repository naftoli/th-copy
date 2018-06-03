import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
import SidebarDropdown from './SidebarDropdown';

class SidebarItem extends Component {

  render(){
    // This is a dropdown
    if ( this.props.children ) {
      return <SidebarDropdown { ...this.props } />;
    }

    // Generate the correct type of link
    let path = "#";

    // generate the path to the correct location
    if ( this.props.legacy ){
      path = LEGACY_URL + this.props.path;
    } else {
      // return react router link
    }

    // return a simple item
    return (
      <li>
        <a href={ path }>
          { this.props.icon }
          { this.props.label }
        </a>
      </li>
    )
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